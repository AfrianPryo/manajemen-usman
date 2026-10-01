<?php

namespace App\Services\Documents;

use App\Models\DocumentTemplate;
use App\Models\OfficialDocument;
use App\Models\SignatureProfile;
use App\Services\Documents\Contracts\DocumentBodyRendererInterface;
use App\Services\Documents\Contracts\DocumentDataProviderInterface;
use App\Services\Documents\Providers\AssetHandoverDataProvider;
use App\Services\Documents\Providers\ConsolidatedReportDataProvider;
use App\Services\Documents\Providers\FinanceReportDataProvider;
use App\Services\Documents\Providers\SuratKeteranganDataProvider;
use App\Services\Documents\Renderers\AssetHandoverBodyRenderer;
use App\Services\Documents\Renderers\ConsolidatedReportBodyRenderer;
use App\Services\Documents\Renderers\FinanceReportBodyRenderer;
use App\Services\Documents\Renderers\SuratKeteranganBodyRenderer;
use App\Services\Documents\Support\SanitizesDocumentText;
use App\Support\DocumentTypes;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Generator dokumen resmi.
 *
 * File yang diunggah admin di Kelola Template (document_templates.file_path) sekarang
 * HANYA berisi kop surat: header/footer (logo, alamat, kontak) dan page setup (margin,
 * ukuran kertas). Badan surat (nomor, perihal, isi, tabel data, sampai blok tanda tangan)
 * dibangun otomatis di sini menggunakan data dari Data Provider + Body Renderer per jenis
 * dokumen, jadi admin tidak perlu lagi menyiapkan placeholder ${...} satu per satu.
 */
class OfficialDocumentGenerator
{
    use SanitizesDocumentText;

    public function __construct(protected DocumentNumberService $numberService)
    {
    }

    protected function resolveProvider(string $type): DocumentDataProviderInterface
    {
        return match ($type) {
            DocumentTypes::FINANCE_REPORT => new FinanceReportDataProvider(),
            DocumentTypes::BERITA_ACARA_ASET => new AssetHandoverDataProvider(),
            DocumentTypes::SURAT_KETERANGAN => new SuratKeteranganDataProvider(),
            DocumentTypes::LAPORAN_KONSOLIDASI => new ConsolidatedReportDataProvider(),
            default => throw new \InvalidArgumentException("Jenis dokumen '{$type}' tidak dikenali."),
        };
    }

    protected function resolveRenderer(string $type): DocumentBodyRendererInterface
    {
        return match ($type) {
            DocumentTypes::FINANCE_REPORT => new FinanceReportBodyRenderer(),
            DocumentTypes::BERITA_ACARA_ASET => new AssetHandoverBodyRenderer(),
            DocumentTypes::SURAT_KETERANGAN => new SuratKeteranganBodyRenderer(),
            DocumentTypes::LAPORAN_KONSOLIDASI => new ConsolidatedReportBodyRenderer(),
            default => throw new \InvalidArgumentException("Jenis dokumen '{$type}' tidak dikenali."),
        };
    }

    /**
     * Generate satu dokumen resmi: tarik data sistem -> beri nomor surat otomatis ->
     * tulis kepala surat & isi & blok tanda tangan ke atas kop surat -> simpan file & riwayat.
     */
    public function generate(
        DocumentTemplate $template,
        array $params,
        SignatureProfile $signature,
        int $generatedByUserId,
        int $attempt = 1
    ): OfficialDocument {
        $provider = $this->resolveProvider($template->type);
        $renderer = $this->resolveRenderer($template->type);

        $data = $provider->build($params);
        $rows = $data['rows'] ?? [];
        unset($data['rows']);

        $documentNumber = $this->numberService->generate($template);

        $disk = Storage::disk('local');

        $relativePath = $this->writeDocumentFile($template, $params, $signature, $documentNumber, $data, $rows, $renderer);

        try {
            return OfficialDocument::create(array_merge(
                $this->documentAttributes($template, $params, $signature, $data, $rows, $relativePath),
                [
                    'document_template_id' => $template->id,
                    'type' => $template->type,
                    'document_number' => $documentNumber,
                    'generated_by' => $generatedByUserId,
                ]
            ));
        } catch (UniqueConstraintViolationException $e) {
            // Buang file .docx yang sudah terlanjur dibuat untuk percobaan yang gagal ini
            $disk->delete($relativePath);

            if ($attempt >= 3) {
                throw $e;
            }

            // Nomor bentrok (sequence tidak sinkron) - coba lagi dari awal dengan nomor berikutnya
            return $this->generate($template, $params, $signature, $generatedByUserId, $attempt + 1);
        }
    }

    /**
     * Perbarui dokumen yang SUDAH ada (dipakai saat satu sesi Buat Dokumen menekan
     * tombol lagi): isi file, tanda tangan, dan metadata ditimpa, tetapi barisnya di
     * riwayat TETAP SATU -- nomor surat dipertahankan dan sequence penomoran tidak
     * bertambah. File lama dihapus setelah yang baru berhasil tersimpan.
     */
    public function regenerate(
        OfficialDocument $document,
        DocumentTemplate $template,
        array $params,
        SignatureProfile $signature
    ): OfficialDocument {
        $provider = $this->resolveProvider($template->type);
        $renderer = $this->resolveRenderer($template->type);

        $data = $provider->build($params);
        $rows = $data['rows'] ?? [];
        unset($data['rows']);

        $disk = Storage::disk('local');
        $oldPath = $document->file_path;

        $relativePath = $this->writeDocumentFile($template, $params, $signature, $document->document_number, $data, $rows, $renderer);

        try {
            $document->update($this->documentAttributes($template, $params, $signature, $data, $rows, $relativePath));
        } catch (\Throwable $e) {
            $disk->delete($relativePath);

            throw $e;
        }

        if ($oldPath && $oldPath !== $relativePath) {
            $disk->delete($oldPath);
        }

        return $document->refresh();
    }

    /**
     * Susun .docx (kop surat + kepala + isi + tanda tangan) lalu simpan ke disk.
     * Mengembalikan path relatif file yang baru ditulis.
     */
    protected function writeDocumentFile(
        DocumentTemplate $template,
        array $params,
        SignatureProfile $signature,
        string $documentNumber,
        array $data,
        array $rows,
        DocumentBodyRendererInterface $renderer
    ): string {
        $disk = Storage::disk('local');

        // Muat kop surat sebagai dokumen dasar. Header/footer & page setup-nya sudah
        // ada di dalamnya; kita tinggal menambahkan konten ke body section pertama.
        $phpWord = IOFactory::load($disk->path($template->file_path));
        $sections = $phpWord->getSections();
        $section = $sections[0] ?? $phpWord->addSection();

        $this->renderLetterHead($section, $documentNumber, $params, $template);
        $renderer->render($section, array_merge($data, ['rows' => $rows]), $params);
        $this->renderSignatureBlock($section, $signature, $disk);

        $outputDir = 'official-documents/' . now()->format('Y/m');
        $fileName = Str::slug($template->name) . '-' . Str::slug($documentNumber) . '-' . time() . '.docx';
        $relativePath = $outputDir . '/' . $fileName;

        $disk->makeDirectory($outputDir);
        IOFactory::createWriter($phpWord, 'Word2007')->save($disk->path($relativePath));

        return $relativePath;
    }

    /**
     * Kolom record yang berubah setiap kali dokumen dibuat ATAU diperbarui.
     * generated_at ikut disegarkan supaya riwayat menampilkan waktu terbaru.
     */
    protected function documentAttributes(
        DocumentTemplate $template,
        array $params,
        SignatureProfile $signature,
        array $data,
        array $rows,
        string $relativePath
    ): array {
        return [
            'title' => $params['title'] ?? $template->name,
            'subject' => $params['subject'] ?? null,
            'recipient' => $params['recipient'] ?? null,
            'unit_id' => $params['unit_id'] ?? null,
            'period_start' => $params['start_date'] ?? null,
            'period_end' => $params['end_date'] ?? null,
            'data_snapshot' => array_merge($data, ['rows' => $rows]),
            'file_path' => $relativePath,
            'signed_by_name' => $signature->name,
            'signed_by_position' => $signature->position,
            'signature_path' => $signature->signature_path,
            'generated_at' => now(),
        ];
    }

    /**
     * Tulis kepala surat: tanggal, nomor, perihal (opsional), kepada Yth (opsional),
     * lalu judul dokumen di tengah. Bagian ini sama untuk semua jenis dokumen sehingga
     * tidak perlu ditulis ulang oleh masing-masing body renderer.
     *
     * $documentNumber dari DocumentNumberService (format token internal) dan $template->name
     * (dikelola admin lewat form terkontrol) relatif aman, tapi tetap disanitasi supaya
     * konsisten dan aman kalau suatu saat sumbernya berubah jadi lebih bebas. $params yang
     * berasal langsung dari input form (subject, recipient, title) WAJIB disanitasi karena
     * bisa memuat '&', '<', '>' yang merusak XML docx.
     */
    protected function renderLetterHead(Section $section, string $documentNumber, array $params, DocumentTemplate $template): void
    {
        $font = ['name' => 'Arial', 'size' => 11];

        $section->addText(now()->translatedFormat('d F Y'), $font, ['alignment' => Jc::END]);
        $section->addTextBreak(1);

        $section->addText('Nomor    : ' . $this->sanitize($documentNumber), $font);

        if (!empty($params['subject'])) {
            $section->addText('Perihal  : ' . $this->sanitize($params['subject']), $font);
        }

        if (!empty($params['recipient'])) {
            $section->addTextBreak(1);
            $section->addText('Kepada Yth.', $font);
            $section->addText($this->sanitize($params['recipient']), array_merge($font, ['bold' => true]));
        }

        $section->addTextBreak(1);

        $title = $params['title'] ?? $template->name;
        $section->addText(
            mb_strtoupper($this->sanitize($title), 'UTF-8'),
            array_merge($font, ['size' => 13, 'bold' => true, 'underline' => 'single']),
            ['alignment' => Jc::CENTER, 'spaceAfter' => 240]
        );
    }

    /**
     * Tulis blok tanda tangan di kanan bawah: jabatan, gambar tanda tangan (kalau ada),
     * lalu nama penandatangan bergaris bawah.
     *
     * $signature->name dan $signature->position diisi lewat form profil tanda tangan -
     * tetap disanitasi karena tetap teks bebas yang bisa memuat karakter spesial XML.
     */
    protected function renderSignatureBlock(Section $section, SignatureProfile $signature, FilesystemAdapter $disk): void
    {
        $font = ['name' => 'Arial', 'size' => 11];

        $section->addTextBreak(2);
        $section->addText($this->sanitize($signature->position), $font, ['alignment' => Jc::END]);

        $signatureImageAdded = false;

        if ($signature->signature_path && $disk->exists($signature->signature_path)) {
            try {
                $section->addImage($disk->path($signature->signature_path), [
                    'width' => 110,
                    'height' => 55,
                    'alignment' => Jc::END,
                ]);
                $signatureImageAdded = true;
            } catch (\Exception $e) {
                // gambar tanda tangan gagal dimuat (file rusak dsb) - lewati, nama tetap dicetak
            }
        }

        if (!$signatureImageAdded) {
            $section->addTextBreak(3);
        }

        $section->addText(
            $this->sanitize($signature->name),
            array_merge($font, ['bold' => true, 'underline' => 'single']),
            ['alignment' => Jc::END]
        );
    }
}
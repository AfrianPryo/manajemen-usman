<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{--
        Heading atas DISAMAKAN dengan pola header "Dashboard Master Admin":
        dibungkus kartu putih ber-border + shadow-sm, judul text-md font-bold
        tracking-tight, subjudul text-[12px] tracking-tight text-neutral-400
        -- sebelumnya judul mengambang tanpa kartu, jadi terasa beda keluarga
        dengan dashboard utama.
    --}}
    <div class="bg-white dark:bg-slate-800 p-5 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <h1 class="text-md font-bold tracking-tight text-neutral-900 dark:text-white">Dokumen Resmi</h1>
        <p class="text-[12px] tracking-tight text-neutral-400 mt-1">
            Menggabungkan data sistem secara otomatis ke template resmi ber-KOP surat dan bertanda tangan — tanpa risiko salah ketik dari copy-paste manual.
        </p>
    </div>

    {{-- Quick Action Cards --}}
    {{--
        Warna badge ikon DISAMAKAN dengan aksen dashboard utama (biru #0d3b74
        untuk aksi utama, netral untuk aksi sekunder) -- sebelumnya tiap kartu
        pakai warna berbeda (merah/sky/amber/emerald) tanpa makna semantik,
        jadi diseragamkan supaya hierarkinya jelas: "Buat Dokumen" adalah aksi
        utama, tiga lainnya adalah aksi pendukung.
    --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('master.documents.generate') }}" class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 hover:border-blue-200 dark:hover:border-blue-800/60 transition-all">
            <span class="w-9 h-9 rounded-sm bg-blue-50 dark:bg-blue-950/40 text-[#0d3b74] dark:text-blue-400 flex items-center justify-center">
                <x-heroicon-o-document-plus class="w-4.5 h-4.5" />
            </span>
            <h3 class="mt-4 text-sm font-bold text-neutral-900 dark:text-white">Buat Dokumen</h3>
            <p class="mt-1 text-[11px] text-neutral-400">Generate dokumen resmi baru dari data sistem.</p>
        </a>

        <a href="{{ route('master.documents.history') }}" class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 hover:border-blue-200 dark:hover:border-blue-800/60 transition-all">
            <span class="w-9 h-9 rounded-sm bg-neutral-100 dark:bg-slate-900 text-neutral-500 dark:text-neutral-400 flex items-center justify-center">
                <x-heroicon-o-clock class="w-4.5 h-4.5" />
            </span>
            <h3 class="mt-4 text-sm font-bold text-neutral-900 dark:text-white">Riwayat Dokumen</h3>
            <p class="mt-1 text-[11px] text-neutral-400">{{ number_format($totalDocuments) }} dokumen sudah dibuat.</p>
        </a>

        <a href="{{ route('master.documents.templates') }}" class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 hover:border-blue-200 dark:hover:border-blue-800/60 transition-all">
            <span class="w-9 h-9 rounded-sm bg-neutral-100 dark:bg-slate-900 text-neutral-500 dark:text-neutral-400 flex items-center justify-center">
                <x-heroicon-o-document-duplicate class="w-4.5 h-4.5" />
            </span>
            <h3 class="mt-4 text-sm font-bold text-neutral-900 dark:text-white">Kelola Template</h3>
            <p class="mt-1 text-[11px] text-neutral-400">Atur template Word ber-KOP surat per jenis dokumen.</p>
        </a>

        <a href="{{ route('master.documents.signature') }}" class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5 hover:border-blue-200 dark:hover:border-blue-800/60 transition-all">
            <span class="w-9 h-9 rounded-sm bg-neutral-100 dark:bg-slate-900 text-neutral-500 dark:text-neutral-400 flex items-center justify-center">
                <x-heroicon-o-pencil-square class="w-4.5 h-4.5" />
            </span>
            <h3 class="mt-4 text-sm font-bold text-neutral-900 dark:text-white">Tanda Tangan</h3>
            <p class="mt-1 text-[11px] text-neutral-400">Kelola gambar tanda tangan &amp; jabatan Anda.</p>
        </a>
    </div>

    {{-- Panduan Singkat --}}
    {{--
        Header section DISAMAKAN dengan pola "Transaksi Terkini" / "Log
        Aktivitas" di Dashboard Master Admin (heading text-base font-bold +
        subjudul + border-b), bukan lagi label kecil huruf kapital.
    --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5">
        <div class="pb-4 border-b border-neutral-100 dark:border-slate-700">
            <h2 class="text-base font-bold text-neutral-900 dark:text-white">Alur Membuat Dokumen Resmi</h2>
            <p class="text-xs text-neutral-400 mt-0.5">Empat langkah singkat dari template sampai dokumen siap diarsipkan</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
            <div class="flex items-start gap-3">
                <span class="shrink-0 w-6 h-6 rounded-sm bg-[#0d3b74] dark:bg-blue-900 text-white text-[11px] font-bold flex items-center justify-center">1</span>
                <div>
                    <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Siapkan Template</p>
                    <p class="text-[11px] text-neutral-400 mt-0.5">Unggah kop surat (.docx) per jenis dokumen di menu Kelola Template.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="shrink-0 w-6 h-6 rounded-sm bg-[#0d3b74] dark:bg-blue-900 text-white text-[11px] font-bold flex items-center justify-center">2</span>
                <div>
                    <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Siapkan Tanda Tangan</p>
                    <p class="text-[11px] text-neutral-400 mt-0.5">Tambahkan profil nama, jabatan, dan gambar tanda tangan Anda.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="shrink-0 w-6 h-6 rounded-sm bg-[#0d3b74] dark:bg-blue-900 text-white text-[11px] font-bold flex items-center justify-center">3</span>
                <div>
                    <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Buat Dokumen</p>
                    <p class="text-[11px] text-neutral-400 mt-0.5">Pilih jenis, template, isi data singkat, lalu pilih penanda tangan.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="shrink-0 w-6 h-6 rounded-sm bg-[#0d3b74] dark:bg-blue-900 text-white text-[11px] font-bold flex items-center justify-center">4</span>
                <div>
                    <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Unduh &amp; Arsip</p>
                    <p class="text-[11px] text-neutral-400 mt-0.5">Dokumen otomatis tercatat di Riwayat Dokumen lengkap nomor suratnya.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Dokumen Terbaru --}}
    {{--
        Header & tautan "Lihat Semua" DISAMAKAN persis dengan pola section
        "Transaksi Terkini" / "Log Aktivitas" di Dashboard Master Admin
        (heading text-base font-bold + border-b, bukan lagi label kecil
        huruf kapital) supaya kedua dashboard terasa satu keluarga desain.
    --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-5">
        <div class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-slate-700">
            <div>
                <h2 class="text-base font-bold text-neutral-900 dark:text-white">Dokumen Terbaru</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Lima dokumen resmi terakhir yang dibuat</p>
            </div>
            <a href="{{ route('master.documents.history') }}" class="text-xs font-bold text-[#0d3b74] dark:text-white hover:text-blue-700 dark:hover:text-neutral-300 transition-colors shrink-0">
                Lihat Semua &rarr;
            </a>
        </div>
        <div class="overflow-x-auto mt-2 -mx-5">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-y border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-5 py-2.5">Judul Dokumen</th>
                        <th class="px-5 py-2.5">No. Surat</th>
                        <th class="px-5 py-2.5 text-right">Dibuat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse ($recentDocuments as $doc)
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-5 py-3 text-xs font-semibold text-neutral-900 dark:text-white">{{ $doc->title }}</td>
                            <td class="px-5 py-3 text-[11px] font-mono text-neutral-400">{{ $doc->document_number }}</td>
                            <td class="px-5 py-3 text-[11px] text-neutral-400 text-right whitespace-nowrap">{{ optional($doc->generated_at)->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-12 text-center text-xs text-neutral-400">Belum ada dokumen resmi yang dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
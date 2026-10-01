<?php

namespace App\Support;

/**
 * Pesan error validasi form yang seragam (Bahasa Indonesia) beserta label
 * field yang ramah pengguna.
 *
 * Dipasang SEKALI di AppServiceProvider lewat Validator::resolver() sebagai
 * nilai DEFAULT. Artinya:
 *   - Pesan/atribut yang sudah ditulis eksplisit di komponen (parameter
 *     $messages / $attributes pada validate(), method messages(), atau
 *     validationAttributes()) TETAP dipakai dan menang atas default ini.
 *   - Rule yang tidak tercantum di sini tetap memakai pesan bawaan framework
 *     (lang/id/validation.php), jadi tidak ada rule yang "hilang" pesannya.
 *
 * Catatan penulisan:
 *   - Semua pesan diawali :Attribute (huruf kapital di awal kalimat) supaya
 *     konsisten walaupun label field ditulis huruf kecil di komponen tertentu.
 *   - Field bertipe pilihan (dropdown/radio/checkbox) memakai kalimat
 *     "wajib dipilih", field berkas memakai "wajib diunggah", sisanya
 *     "wajib diisi".
 */
class ValidationMessages
{
    /**
     * Pesan default per rule + pesan "wajib" khusus per jenis field.
     */
    public static function messages(): array
    {
        $messages = [
            // --- Wajib diisi -------------------------------------------------
            'required'         => ':Attribute wajib diisi.',
            'required_if'      => ':Attribute wajib diisi.',
            'required_unless'  => ':Attribute wajib diisi.',
            'required_with'    => ':Attribute wajib diisi.',
            'required_without' => ':Attribute wajib diisi.',
            'filled'           => ':Attribute tidak boleh dikosongkan.',

            // --- Format ------------------------------------------------------
            'email'       => ':Attribute tidak valid. Gunakan format seperti nama@contoh.com.',
            'url'         => ':Attribute tidak valid. Gunakan alamat lengkap, misalnya https://contoh.com.',
            'regex'       => ':Attribute memiliki format yang tidak valid.',
            'date'        => ':Attribute harus berupa tanggal yang valid.',
            'date_format' => ':Attribute harus sesuai format :format.',
            'string'      => ':Attribute harus berupa teks.',
            'numeric'     => ':Attribute harus berupa angka.',
            'integer'     => ':Attribute harus berupa bilangan bulat.',
            'boolean'     => ':Attribute tidak valid.',
            'array'       => ':Attribute tidak valid.',
            'digits'         => ':Attribute harus terdiri dari :digits digit angka.',
            'digits_between' => ':Attribute harus terdiri dari :min sampai :max digit angka.',

            // --- Rentang / panjang ------------------------------------------
            'min' => [
                'numeric' => ':Attribute minimal :min.',
                'string'  => ':Attribute minimal :min karakter.',
                'array'   => ':Attribute minimal terdiri dari :min item.',
                'file'    => 'Ukuran :attribute minimal :min KB.',
            ],
            'max' => [
                'numeric' => ':Attribute maksimal :max.',
                'string'  => ':Attribute maksimal :max karakter.',
                'array'   => ':Attribute maksimal terdiri dari :max item.',
                'file'    => 'Ukuran :attribute maksimal :max KB.',
            ],
            'between' => [
                'numeric' => ':Attribute harus berada di antara :min dan :max.',
                'string'  => ':Attribute harus terdiri dari :min sampai :max karakter.',
                'array'   => ':Attribute harus terdiri dari :min sampai :max item.',
                'file'    => 'Ukuran :attribute harus antara :min dan :max KB.',
            ],

            // --- Pilihan / keunikan / perbandingan --------------------------
            'in'        => ':Attribute yang dipilih tidak valid.',
            'not_in'    => ':Attribute yang dipilih tidak valid.',
            'exists'    => ':Attribute yang dipilih tidak valid.',
            'unique'    => ':Attribute sudah terdaftar.',
            'confirmed' => ':Attribute dan konfirmasinya tidak sama.',
            'same'      => ':Attribute harus sama dengan :other.',
            'different' => ':Attribute tidak boleh sama dengan :other.',
            'distinct'  => ':Attribute memiliki nilai ganda.',
            'accepted'  => ':Attribute harus disetujui.',

            'after'           => ':Attribute harus setelah :date.',
            'after_or_equal'  => ':Attribute tidak boleh sebelum :date.',
            'before'          => ':Attribute harus sebelum :date.',
            'before_or_equal' => ':Attribute tidak boleh setelah :date.',

            // --- Berkas ------------------------------------------------------
            'file'     => ':Attribute harus berupa berkas.',
            'image'    => ':Attribute harus berupa file gambar.',
            'mimes'    => ':Attribute harus berformat :values.',
            'mimetypes' => ':Attribute harus berformat :values.',
            'uploaded' => ':Attribute gagal diunggah. Pastikan ukuran berkas tidak melebihi batas yang diizinkan.',
        ];

        // Field pilihan: "wajib dipilih" (lebih tepat daripada "wajib diisi").
        // Pesan per-field mengalahkan pesan rule generik, tetapi tetap kalah
        // dari pesan eksplisit yang ditulis di komponen (key yang sama).
        foreach (self::selectFields() as $field) {
            foreach (self::requiredRules() as $rule) {
                $messages["{$field}.{$rule}"] = ':Attribute wajib dipilih.';
            }
        }

        // Field berkas: "wajib diunggah".
        foreach (self::fileFields() as $field) {
            foreach (self::requiredRules() as $rule) {
                $messages["{$field}.{$rule}"] = ':Attribute wajib diunggah.';
            }
        }

        return $messages;
    }

    /**
     * Label field -> nama yang tampil di pesan error.
     * Field yang tidak terdaftar memakai label bawaan framework.
     */
    public static function attributes(): array
    {
        return [
            // Umum
            'name'            => 'Nama',
            'title'           => 'Judul',
            'message'         => 'Pesan',
            'subject'         => 'Subjek',
            'description'     => 'Deskripsi',
            'notes'           => 'Catatan',
            'address'         => 'Alamat',
            'email'           => 'Email',
            'phone'           => 'Nomor telepon',
            'website'         => 'Website',
            'status'          => 'Status',
            'type'            => 'Tipe',
            'category'        => 'Kategori',
            'unit_id'         => 'Unit Usaha',
            'is_active'       => 'Status aktif',
            'badge'           => 'Label',
            'location'        => 'Lokasi',
            'position'        => 'Jabatan',
            'department'      => 'Departemen / Jurusan',
            'gender'          => 'Jenis kelamin',
            'birth_date'      => 'Tanggal lahir',
            'start_date'      => 'Tanggal mulai',
            'end_date'        => 'Tanggal selesai',
            'frequency'       => 'Frekuensi',
            'amount'          => 'Nominal',
            'price'           => 'Biaya jasa',
            'payment_method'  => 'Metode pembayaran',
            'finance_category_id' => 'Kategori keuangan',
            'vendor_id'       => 'Vendor / Supplier',
            'assigned_to'     => 'Petugas / penanggung jawab',
            'scheduled_at'    => 'Jadwal pengerjaan',
            'service_name'    => 'Nama layanan',
            'customer_name'   => 'Nama pelanggan',
            'customer_phone'  => 'Nomor telepon',
            'contact_name'    => 'Kontak utama (PIC)',
            'pic_name'        => 'Nama PIC',
            'id_number'       => 'ID / NIK / NPWP',
            'proofFile'       => 'Bukti transaksi',
            'items'           => 'Item',
            'items.*.name'       => 'Nama item',
            'items.*.product_id' => 'Produk',
            'items.*.qty'        => 'Jumlah',
            'items.*.unit_price' => 'Harga satuan',

            // Akun, kredensial & OTP
            'identity'                  => 'Username / NIP',
            'username'                  => 'Username',
            'password'                  => 'Password',
            'current_password'          => 'Password saat ini',
            'currentPassword'           => 'Password saat ini',
            'new_password'              => 'Password baru',
            'new_password_confirmation' => 'Konfirmasi password baru',
            'newPassword'               => 'Password baru',
            'newPassword_confirmation'  => 'Konfirmasi password baru',
            'phoneChangePassword'       => 'Password',
            'otp'                       => 'Kode OTP',
            'passwordOtp'               => 'Kode OTP',
            'phoneOtp'                  => 'Kode OTP',
            'newPhone'                  => 'Nomor WhatsApp baru',
            'nip'                       => 'NIP',
            'employee_status'           => 'Status kepegawaian',
            'employeeStatus'            => 'Status kepegawaian',
            'role'                      => 'Role',
            'admin_name'                => 'Nama lengkap',
            'admin_phone'               => 'Nomor HP / WhatsApp',
            'admin_unit_id'             => 'Unit Usaha',
            'avatar'                    => 'Foto profil',
            'recipientType'             => 'Target penerima',
            'selectedUserIds'           => 'Penerima pengumuman',

            // Inventori & transaksi
            'form_name'                 => 'Nama produk',
            'form_code'                 => 'Kode produk / SKU',
            'form_category_id'          => 'Kategori produk',
            'form_unit_type'            => 'Satuan unit',
            'form_unit_id'              => 'Unit Usaha',
            'form_type'                 => 'Tipe transaksi',
            'form_status'               => 'Status',
            'form_stock'                => 'Jumlah stok',
            'form_min_stock'            => 'Batas minimum stok',
            'form_purchase_price'       => 'Harga beli',
            'form_selling_price'        => 'Harga jual',
            'form_description'          => 'Deskripsi',
            'form_image'                => 'Foto produk',
            'form_amount'               => 'Jumlah nominal',
            'form_transaction_date'     => 'Tanggal transaksi',
            'form_reference_no'         => 'No. referensi',
            'form_payment_method'       => 'Metode pembayaran',
            'form_finance_category_id'  => 'Kategori transaksi',
            'form_proof_file'           => 'Bukti transaksi',
            'stock_type'                => 'Aksi stok',
            'stock_quantity'            => 'Jumlah unit',
            'stock_note'                => 'Catatan stok',
            'category_name'             => 'Nama kategori',
            'category_type'             => 'Tipe kategori',
            'category_scope'            => 'Cakupan kategori',
            'category_unit_id'          => 'Unit Usaha',
            'category_unit_ids'         => 'Unit Usaha',
            'importFile'                => 'Berkas impor',
            'excel_file'                => 'Berkas Excel',

            // Aset & kontrak
            'asset_tag'            => 'Tag / kode aset',
            'serial_number'        => 'Nomor seri',
            'condition'            => 'Kondisi',
            'purchase_date'        => 'Tanggal pembelian',
            'purchase_cost'        => 'Harga beli',
            'contract_start_date'  => 'Tanggal mulai kontrak',
            'contract_end_date'    => 'Tanggal selesai kontrak',
            'asset_ids'            => 'Aset yang diserahterimakan',

            // Dokumen resmi
            'templateId'            => 'Template',
            'templateFile'          => 'File kop surat',
            'signatureId'           => 'Tanda tangan',
            'signatureImage'        => 'Gambar tanda tangan',
            'numbering_format'      => 'Format nomor surat',
            'numbering_reset'       => 'Reset nomor',
            'recipient'             => 'Penerima',
            'keperluan'             => 'Keperluan',
            'isi_keterangan'        => 'Isi keterangan',
            'nama_penerima'         => 'Nama penerima',
            'nip_penerima'          => 'NIP penerima',
            'jabatan_penerima'      => 'Jabatan penerima',
            'pihak_pertama_nama'    => 'Nama pihak pertama',
            'pihak_pertama_jabatan' => 'Jabatan pihak pertama',
            'pihak_kedua_nama'      => 'Nama pihak kedua',
            'pihak_kedua_jabatan'   => 'Jabatan pihak kedua',

            // Keamanan & pengaturan
            'blockType'                   => 'Tipe blokir',
            'blockValue'                  => 'Alamat IP / perangkat',
            'blockReason'                 => 'Alasan',
            'logRetentionDays'            => 'Batas retensi log',
            'retentionInput'              => 'Batas retensi',
            'appName'                     => 'Nama aplikasi',
            'logo'                        => 'Logo',
            'defaultCategory'             => 'Kategori Unit Usaha default',
            'waProvider'                  => 'Provider WhatsApp',
            'waSenderNumber'              => 'Nomor WhatsApp pengirim',
            'waApiKey'                    => 'API key / token',
            'sessionTimeoutMasterMinutes' => 'Durasi sesi Admin Master',
            'sessionTimeoutUnitMinutes'   => 'Durasi sesi Admin Unit',
            'reportRoutineTime'           => 'Jam pengiriman',
            'reportRoutineFrequency'      => 'Frekuensi pengiriman',
            'reportRoutineDayOfWeek'      => 'Hari pengiriman',
            'reportRoutineDayOfMonth'     => 'Tanggal pengiriman',
            'reportRoutineSections'       => 'Bagian laporan',

            // Konten landing page
            'landingHeroTitleTop'               => 'Judul baris atas',
            'landingHeroTitleBottom'            => 'Judul baris bawah',
            'landingHeroScrollText'             => 'Label scroll indicator',
            'landingTentangTitle'               => 'Judul bagian Tentang',
            'landingTentangDescription'         => 'Deskripsi bagian Tentang',
            'landingTentangPhoto'               => 'Foto bagian Tentang',
            'landingMitraTitle'                 => 'Judul bagian Mitra',
            'landingMitraDescription'           => 'Deskripsi bagian Mitra',
            'landingSelectedUnitIds'            => 'Unit Usaha yang ditampilkan',
            'landingFiturEyebrow'               => 'Label kecil bagian Fitur',
            'landingFiturTitle'                 => 'Judul bagian Fitur',
            'landingFiturItems'                 => 'Daftar fitur',
            'landingFiturItems.*.title'         => 'Judul fitur',
            'landingFiturItems.*.description'   => 'Deskripsi fitur',
            'landingFaqTitle'                   => 'Judul bagian FAQ',
            'landingFaqItems'                   => 'Daftar FAQ',
            'landingFaqItems.*.question'        => 'Pertanyaan',
            'landingFaqItems.*.answer'          => 'Jawaban',
            'landingCaraKerjaTitle'             => 'Judul bagian Cara Kerja',
            'landingCaraKerjaDescription'       => 'Deskripsi bagian Cara Kerja',
            'landingCaraKerjaItems'             => 'Daftar langkah',
            'landingCaraKerjaItems.*.title'       => 'Judul langkah',
            'landingCaraKerjaItems.*.description' => 'Deskripsi langkah',
            'landingCaraKerjaItems.*.icon'        => 'Ikon langkah',
            'landingCaraKerjaItems.*.badge'       => 'Label langkah',
            'landingFooterTitle'                => 'Judul footer',
        ];
    }

    /**
     * Field yang diisi lewat dropdown / radio / daftar pilihan.
     */
    private static function selectFields(): array
    {
        return [
            'unit_id', 'admin_unit_id', 'form_unit_id', 'category_unit_id', 'category_unit_ids',
            'category', 'form_category_id', 'finance_category_id', 'form_finance_category_id',
            'category_type', 'category_scope',
            'type', 'form_type', 'status', 'form_status', 'condition', 'frequency',
            'payment_method', 'form_payment_method',
            'vendor_id', 'role', 'gender',
            'employee_status', 'employeeStatus', 'recipientType', 'selectedUserIds',
            'stock_type', 'blockType', 'defaultCategory', 'waProvider', 'numbering_reset',
            'templateId', 'signatureId', 'asset_ids', 'landingSelectedUnitIds',
            'reportRoutineFrequency', 'reportRoutineDayOfWeek',
            'items.*.product_id',
        ];
    }

    /**
     * Field berkas unggahan.
     */
    private static function fileFields(): array
    {
        return [
            'avatar', 'logo', 'importFile', 'excel_file', 'templateFile', 'signatureImage',
            'proofFile', 'form_proof_file', 'form_image', 'landingTentangPhoto',
        ];
    }

    private static function requiredRules(): array
    {
        return ['required', 'required_if', 'required_unless', 'required_with', 'required_without'];
    }
}

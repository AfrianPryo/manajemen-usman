<div class="p-6 max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Sistem</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola informasi sekolah, preferensi aplikasi, dan konfigurasi fitur.</p>
    </div>

    <!-- Alert Success -->
    @if (session()->has('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm flex items-center justify-between">
            <span class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </span>
        </div>
    @endif

    <!-- Navigasi Tab -->
    <div class="border-b border-gray-200 dark:border-slate-700">
        <nav class="flex space-x-6" aria-label="Tabs">
            <button wire:click="setTab('profile')" 
                class="py-3 px-1 border-b-2 font-medium text-sm transition flex items-center gap-2 {{ $activeTab === 'profile' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-slate-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 v5"/></svg>
                Profil Sekolah
            </button>

            <button wire:click="setTab('preferences')" 
                class="py-3 px-1 border-b-2 font-medium text-sm transition flex items-center gap-2 {{ $activeTab === 'preferences' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-slate-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                Preferensi Sistem
            </button>

            <button wire:click="setTab('features')" 
                class="py-3 px-1 border-b-2 font-medium text-sm transition flex items-center gap-2 {{ $activeTab === 'features' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-slate-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Fitur & Modul
            </button>
        </nav>
    </div>

    <!-- TAB 1: PROFIL SEKOLAH -->
    @if($activeTab === 'profile')
        <form wire:submit="saveProfile" class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6 space-y-6">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Identitas Sekolah</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Atur informasi utama instansi sekolah dan logo resmi.</p>
            </div>

            <!-- Logo Sekolah -->
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden bg-gray-50 dark:bg-slate-900 flex items-center justify-center">
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-contain p-1">
                    @elseif ($existingLogo)
                        <img src="{{ asset('storage/' . $existingLogo) }}" class="w-full h-full object-contain p-1">
                    @else
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 v5"/></svg>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Logo Sekolah</label>
                    <input type="file" wire:model="logo" accept="image/*" class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-slate-700 dark:file:text-gray-300">
                    @error('logo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Sekolah</label>
                    <input type="text" wire:model="schoolName" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white">
                    @error('schoolName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NPSN</label>
                    <input type="text" wire:model="npsn" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white">
                    @error('npsn') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kepala Sekolah</label>
                    <input type="text" wire:model="principalName" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white">
                    @error('principalName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Sekolah</label>
                    <input type="email" wire:model="schoolEmail" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white">
                    @error('schoolEmail') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. Telepon Sekolah</label>
                    <input type="text" wire:model="schoolPhone" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white">
                    @error('schoolPhone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Lengkap</label>
                    <textarea wire:model="schoolAddress" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white"></textarea>
                    @error('schoolAddress') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-slate-700">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition flex items-center gap-2">
                    <span wire:loading.remove wire:target="saveProfile">Simpan Profil Sekolah</span>
                    <span wire:loading wire:target="saveProfile">Menyimpan...</span>
                </button>
            </div>
        </form>
    @endif

    <!-- TAB 2: PREFERENSI SISTEM -->
    @if($activeTab === 'preferences')
        <form wire:submit="savePreferences" class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6 space-y-6">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Parameter Aplikasi</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Atur batasan waktu sesi, format mata uang, dan jumlah tampilan data.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Aplikasi</label>
                    <input type="text" wire:model="appName" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white">
                    @error('appName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Simbol Mata Uang</label>
                    <input type="text" wire:model="currencySymbol" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white">
                    @error('currencySymbol') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Session Timeout (Menit)</label>
                    <input type="number" wire:model="sessionTimeout" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white">
                    @error('sessionTimeout') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Data per Halaman (Pagination)</label>
                    <select wire:model="itemsPerPage" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white">
                        <option value="5">5 Item</option>
                        <option value="10">10 Item</option>
                        <option value="25">25 Item</option>
                        <option value="50">50 Item</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Zona Waktu</label>
                    <select wire:model="timezone" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white">
                        <option value="Asia/Jakarta">WIB (Asia/Jakarta)</option>
                        <option value="Asia/Makassar">WITA (Asia/Makassar)</option>
                        <option value="Asia/Jayapura">WIT (Asia/Jayapura)</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" id="maintenanceMode" wire:model="maintenanceMode" class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                    <label for="maintenanceMode" class="text-sm text-gray-700 dark:text-gray-300">
                        Aktifkan Mode Perbaikan (Maintenance)
                    </label>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-slate-700">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition flex items-center gap-2">
                    <span wire:loading.remove wire:target="savePreferences">Simpan Preferensi</span>
                    <span wire:loading wire:target="savePreferences">Menyimpan...</span>
                </button>
            </div>
        </form>
    @endif

    <!-- TAB 3: FITUR & MODUL -->
    @if($activeTab === 'features')
        <form wire:submit="saveFeatures" class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6 space-y-6">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Akses Fitur & Otomatisasi</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Atur modul aktif dan hak akses umum untuk unit usaha.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori Unit Usaha Default</label>
                    <select wire:model="defaultCategory" class="w-full md:w-1/2 px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm text-gray-900 dark:text-white">
                        <option value="ritel">Ritel (Produk / Toko)</option>
                        <option value="jasa">Jasa / Layanan</option>
                    </select>
                    @error('defaultCategory') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4 space-y-3 border-t border-gray-100 dark:border-slate-700">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="allowMultiUnitAdmin" class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                        <div>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Izinkan Admin Mengelola Banyak Unit</span>
                            <p class="text-xs text-gray-400">Satu akun admin unit dapat ditugaskan ke lebih dari 1 unit usaha.</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="enableWaNotifications" class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                        <div>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Notifikasi WhatsApp</span>
                            <p class="text-xs text-gray-400">Kirim laporan harian/transaksi dan pemberitahuan penting langsung ke nomor WhatsApp sekolah.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-slate-700">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition flex items-center gap-2">
                    <span wire:loading.remove wire:target="saveFeatures">Simpan Fitur</span>
                    <span wire:loading wire:target="saveFeatures">Menyimpan...</span>
                </button>
            </div>
        </form>
    @endif
</div>
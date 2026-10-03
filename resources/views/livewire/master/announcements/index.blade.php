<div class="w-full max-w-[1670px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">
    <livewire:page-tour tour="announcements.index" />

    {{-- Flash Notification (toast) --}}
    @if (session()->has('message'))
        <div wire:key="toast-message-{{ md5(session('message')) }}" x-data x-init="$store.toast.push('success', @js(session('message')))"></div>
    @endif

    {{-- ================= HEADER & QUICK ACTIONS ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-md font-bold tracking-tight text-neutral-900 dark:text-white tracking-tight">Pengumuman</h1>
            </div>
            <p class="text-[12px] tracking-tight text-neutral-400 mt-1">Kirim pengumuman ke semua atau admin unit tertentu, lewat notifikasi sistem &amp; opsional WhatsApp (Fonnte).</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" wire:click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all shadow-sm shadow-blue-900/20 cursor-pointer">
                <x-heroicon-o-plus class="w-4 h-4" />
                <span>Buat Pengumuman</span>
            </button>
        </div>
    </div>

    {{-- ================= KARTU STATISTIK ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Admin Unit Aktif (calon penerima) --}}
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Admin Unit Aktif Saat Ini</p>
                <x-heroicon-o-users stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $recipientsCount }}</p>
            <p class="mt-1 text-[11px] text-neutral-400">Penerima pengumuman berikutnya.</p>
        </div>

        {{-- Total Pengumuman Terkirim --}}
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Total Pengumuman Terkirim</p>
                <x-heroicon-o-megaphone stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $announcements->total() }}</p>
            <p class="mt-1 text-[11px] text-neutral-400">Seluruh riwayat pengiriman.</p>
        </div>
    </div>

    {{-- Riwayat Pengumuman --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="p-5 border-b border-neutral-100 dark:border-slate-700">
            <h2 class="text-base font-bold text-neutral-900 dark:text-white">Riwayat Pengumuman</h2>
            <p class="text-xs text-neutral-400">Daftar pengumuman yang pernah dikirim ke admin unit</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-5 py-3.5">Judul</th>
                        <th class="px-5 py-3.5">Pesan</th>
                        <th class="px-5 py-3.5">Dikirim Oleh</th>
                        <th class="px-5 py-3.5 text-center">Penerima</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($announcements as $announcement)
                        <tr wire:key="announcement-{{ $announcement->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors align-top">
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-neutral-900 dark:text-white text-[13px] leading-tight">{{ $announcement->title }}</div>
                                @php
                                    $__badgeCls = match ($announcement->badge) {
                                        'Penting'   => 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400',
                                        'Pengingat' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400',
                                        default     => 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400',
                                    };
                                @endphp
                                <span class="inline-block mt-1.5 px-2 py-0.5 text-[10px] font-bold tracking-wide rounded-sm {{ $__badgeCls }}">{{ $announcement->badge }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-[12px] text-neutral-600 dark:text-neutral-300 max-w-md">
                                <p class="line-clamp-2">{{ $announcement->message }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-[12px] text-neutral-600 dark:text-neutral-300 whitespace-nowrap">
                                {{ $announcement->sender?->name ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-center text-[12px] font-semibold text-neutral-800 dark:text-neutral-100">
                                {{ $announcement->recipients_count }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-[12px] text-neutral-600 dark:text-neutral-300">
                                {{ $announcement->created_at?->translatedFormat('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-xs text-neutral-400">
                                Belum ada pengumuman yang pernah dikirim.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-neutral-100 dark:border-slate-700 bg-neutral-50/40 dark:bg-slate-900/40 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                @if($announcements->total() > 0)
                    Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $announcements->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $announcements->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $announcements->total() }}</span> total pengumuman
                @endif
            </div>
            <div class="w-full md:w-auto flex justify-end">
                {{ $announcements->links('components.custom-pagination') }}
            </div>
        </div>
    </div>

    {{-- Modal Form Buat Pengumuman --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-sm border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">

                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Buat Pengumuman</h3>
                        <p class="text-xs text-neutral-400">Akan dikirim ke {{ $targetCount }} admin unit lewat notifikasi.</p>
                    </div>
                    <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                <form wire:submit.prevent="send" class="p-6 text-xs">
                    <x-form-tabs tab1-label="Isi Pengumuman" tab2-label="Penerima & Pengiriman" cancel="closeModal" compact rounded="rounded-sm">
                    <x-slot:tab1>
                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Judul <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="title"
                               class="@error('title') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                               placeholder="Contoh: Libur Nasional 17 Agustus" aria-invalid="@error('title') true @else false @enderror" aria-required="true">
                        @error('title') <x-form-error :message="$message" :field="'title'" /> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Label <span class="text-red-500">*</span></label>
                        <select wire:model="badge"
                                class="@error('badge') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2 border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500 cursor-pointer" aria-invalid="@error('badge') true @else false @enderror" aria-required="true">
                            <option value="Pengumuman">Pengumuman</option>
                            <option value="Penting">Penting</option>
                            <option value="Pengingat">Pengingat</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Pesan <span class="text-red-500">*</span></label>
                        <textarea wire:model="message" rows="5"
                                  class="@error('message') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                  placeholder="Isi pengumuman untuk seluruh admin unit..." aria-invalid="@error('message') true @else false @enderror" aria-required="true"></textarea>
                        @error('message') <x-form-error :message="$message" :field="'message'" /> @enderror
                    </div>

                    </x-slot:tab1>
                    <x-slot:tab2>
                    {{-- Target Penerima --}}
                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1.5">Target Penerima <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 px-3 py-2 border rounded-sm cursor-pointer transition-all {{ $recipientType === 'all' ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-950/30' : 'border-neutral-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="recipientType" value="all" class="@error('recipientType') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror text-blue-600 focus:ring-blue-500" aria-invalid="@error('recipientType') true @else false @enderror" aria-required="true">
                                <span class="font-medium text-neutral-700 dark:text-neutral-200">Semua Admin Unit</span>
                            </label>
                            <label class="flex items-center gap-2 px-3 py-2 border rounded-sm cursor-pointer transition-all {{ $recipientType === 'specific' ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-950/30' : 'border-neutral-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="recipientType" value="specific" class="text-blue-600 focus:ring-blue-500" aria-required="true">
                                <span class="font-medium text-neutral-700 dark:text-neutral-200">Admin Tertentu</span>
                            </label>
                        </div>
                        @error('recipientType') <x-form-error :message="$message" :field="'recipientType'" /> @enderror
                    </div>

                    {{-- Daftar Pilihan Admin (hanya tampil kalau target = specific) --}}
                    @if($recipientType === 'specific')
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1.5">Pilih Admin Unit <span class="text-red-500">*</span></label>
                            <div class="border border-neutral-200 dark:border-slate-700 rounded-sm max-h-48 overflow-y-auto divide-y divide-neutral-100 dark:divide-slate-700">
                                @forelse($activeUnitAdmins as $admin)
                                    <label class="flex items-center justify-between gap-3 px-3 py-2 hover:bg-neutral-50 dark:hover:bg-slate-700/40 cursor-pointer">
                                        <span class="flex items-center gap-2 min-w-0">
                                            <input type="checkbox" wire:model="selectedUserIds" value="{{ $admin->id }}" aria-invalid="@error('selectedUserIds') true @else false @enderror" class="rounded text-blue-600 focus:ring-blue-500 shrink-0">
                                            <span class="min-w-0">
                                                <span class="block font-medium text-neutral-800 dark:text-neutral-100 truncate">{{ $admin->name }}</span>
                                                <span class="block text-[10px] text-neutral-400">{{ $admin->unit->name ?? '-' }}</span>
                                            </span>
                                        </span>
                                        @if($admin->phone)
                                            <span class="text-[10px] font-mono text-neutral-400 shrink-0">{{ $admin->phone }}</span>
                                        @else
                                            <span class="text-[10px] text-amber-600 shrink-0">No. HP kosong</span>
                                        @endif
                                    </label>
                                @empty
                                    <p class="px-3 py-4 text-center text-neutral-400">Tidak ada admin unit aktif.</p>
                                @endforelse
                            </div>
                            @error('selectedUserIds') <x-form-error :message="$message" :field="'selectedUserIds'" /> @enderror
                        </div>
                    @endif

                    {{-- Kirim juga via WhatsApp (Fonnte) --}}
                    <div class="p-3 rounded-sm border border-neutral-200 dark:border-slate-700 bg-neutral-50/60 dark:bg-slate-900/40">
                        <x-toggle wire:model="sendViaWhatsapp"
                            label="Kirim juga lewat WhatsApp (Fonnte)"
                            description="Pesan akan dikirim langsung ke nomor HP/WhatsApp yang terdaftar pada masing-masing akun admin. Admin tanpa nomor HP terdaftar akan dilewati." />
                    </div>

                    </x-slot:tab2>
                    <x-slot:submit>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                            <span wire:loading.remove>Kirim ke {{ $targetCount }} Admin Unit</span>
                            <span wire:loading>Mengirim...</span>
                        </button>
                    </x-slot:submit>
                    </x-form-tabs>
                </form>

            </div>
        </div>
    @endif

</div>
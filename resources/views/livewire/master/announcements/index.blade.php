<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('message') }}</span>
            <button @click="show = false" type="button" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300">&times;</button>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Pengumuman</h1>
            <p class="text-xs text-neutral-400 mt-0.5">Kirim pengumuman ke semua atau admin unit tertentu, lewat notifikasi sistem &amp; opsional WhatsApp (Fonnte).</p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <button wire:click="openCreateModal"
                    class="px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-[3px] transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Buat Pengumuman</span>
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02] max-w-xs">
        <p class="text-xs font-medium text-neutral-400">Admin Unit Aktif Saat Ini</p>
        <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight mt-2">{{ $recipientsCount }}</p>
        <p class="text-[11px] text-neutral-400 mt-1">Penerima pengumuman berikutnya.</p>
    </div>

    {{-- Riwayat Pengumuman --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3">Judul</th>
                        <th class="px-4 py-3">Pesan</th>
                        <th class="px-4 py-3">Dikirim Oleh</th>
                        <th class="px-4 py-3 text-center">Penerima</th>
                        <th class="px-4 py-3">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($announcements as $announcement)
                        <tr wire:key="announcement-{{ $announcement->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors align-top">
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-neutral-900 dark:text-white text-[13px] leading-tight">{{ $announcement->title }}</div>
                                <span class="inline-block mt-1 text-[10px] font-medium px-2 py-0.5 rounded-full border bg-sky-100 text-sky-700 border-sky-200">{{ $announcement->badge }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-[12px] text-neutral-600 dark:text-neutral-300 max-w-md">
                                <p class="line-clamp-2">{{ $announcement->message }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-[12px] text-neutral-600 dark:text-neutral-300 whitespace-nowrap">
                                {{ $announcement->sender?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center text-[12px] font-semibold text-neutral-800 dark:text-neutral-100">
                                {{ $announcement->recipients_count }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-[12px] text-neutral-600 dark:text-neutral-300">
                                {{ $announcement->created_at?->translatedFormat('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Belum ada pengumuman yang pernah dikirim.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-neutral-100 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
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
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">

                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Buat Pengumuman</h3>
                        <p class="text-xs text-neutral-400">Akan dikirim ke {{ $targetCount }} admin unit lewat notifikasi.</p>
                    </div>
                    <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                <form wire:submit.prevent="send" class="p-6 space-y-4 text-xs">

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Judul <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="title"
                               class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                               placeholder="Contoh: Libur Nasional 17 Agustus">
                        @error('title') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Label</label>
                        <select wire:model="badge"
                                class="w-full px-3 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500 cursor-pointer">
                            <option value="Pengumuman">Pengumuman</option>
                            <option value="Penting">Penting</option>
                            <option value="Pengingat">Pengingat</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Pesan <span class="text-red-500">*</span></label>
                        <textarea wire:model="message" rows="5"
                                  class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                  placeholder="Isi pengumuman untuk seluruh admin unit..."></textarea>
                        @error('message') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Target Penerima --}}
                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1.5">Target Penerima <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 px-3 py-2 border rounded-md cursor-pointer transition-all {{ $recipientType === 'all' ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-950/30' : 'border-neutral-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="recipientType" value="all" class="text-blue-600 focus:ring-blue-500">
                                <span class="font-medium text-neutral-700 dark:text-neutral-200">Semua Admin Unit</span>
                            </label>
                            <label class="flex items-center gap-2 px-3 py-2 border rounded-md cursor-pointer transition-all {{ $recipientType === 'specific' ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-950/30' : 'border-neutral-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="recipientType" value="specific" class="text-blue-600 focus:ring-blue-500">
                                <span class="font-medium text-neutral-700 dark:text-neutral-200">Admin Tertentu</span>
                            </label>
                        </div>
                        @error('recipientType') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Daftar Pilihan Admin (hanya tampil kalau target = specific) --}}
                    @if($recipientType === 'specific')
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1.5">Pilih Admin Unit <span class="text-red-500">*</span></label>
                            <div class="border border-neutral-200 dark:border-slate-700 rounded-md max-h-48 overflow-y-auto divide-y divide-neutral-100 dark:divide-slate-700">
                                @forelse($activeUnitAdmins as $admin)
                                    <label class="flex items-center justify-between gap-3 px-3 py-2 hover:bg-neutral-50 dark:hover:bg-slate-700/40 cursor-pointer">
                                        <span class="flex items-center gap-2 min-w-0">
                                            <input type="checkbox" wire:model="selectedUserIds" value="{{ $admin->id }}" class="rounded text-blue-600 focus:ring-blue-500 shrink-0">
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
                            @error('selectedUserIds') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Kirim juga via WhatsApp (Fonnte) --}}
                    <div class="p-3 rounded-md border border-neutral-200 dark:border-slate-700 bg-neutral-50/60 dark:bg-slate-900/40">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" wire:model="sendViaWhatsapp" class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                            <span>
                                <span class="block font-semibold text-neutral-700 dark:text-neutral-200">Kirim juga lewat WhatsApp (Fonnte)</span>
                                <span class="block text-[11px] text-neutral-400 mt-0.5">Pesan akan dikirim langsung ke nomor HP/WhatsApp yang terdaftar pada masing-masing akun admin. Admin tanpa nomor HP terdaftar akan dilewati.</span>
                            </span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-md hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                            <span wire:loading.remove>Kirim ke {{ $targetCount }} Admin Unit</span>
                            <span wire:loading>Mengirim...</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif

</div>

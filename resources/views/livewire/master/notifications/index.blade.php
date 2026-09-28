<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Toast untuk hasil aksi (approve/reject/detail gagal dibuka, dst) --}}
    <x-notifications.flash />

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Notifikasi</h1>
            <p class="text-xs text-neutral-400 mt-0.5">Arsip lengkap notifikasi akun Anda, termasuk yang sudah dibaca.</p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button"
                    wire:click="markAllAsRead"
                    @if ($unreadCount === 0) disabled @endif
                    class="px-4 py-2 text-xs font-bold text-neutral-700 dark:text-neutral-200 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 hover:bg-neutral-50 dark:hover:bg-slate-700 rounded-[3px] transition-all flex items-center gap-2 shadow-sm disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                <x-heroicon-o-check class="w-4 h-4" stroke-width="2" />
                <span>Tandai Semua Dibaca</span>
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">

            {{-- Filter Status Baca --}}
            <div class="inline-flex items-center bg-neutral-50 dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[3px] p-0.5 text-xs font-semibold self-start">
                @foreach ([
                    'all'    => ['Semua', $totalCount],
                    'unread' => ['Belum Dibaca', $unreadCount],
                    'read'   => ['Sudah Dibaca', $totalCount - $unreadCount],
                ] as $value => [$label, $count])
                    <button type="button"
                            wire:click="$set('statusFilter', '{{ $value }}')"
                            class="px-3 py-1.5 rounded-[3px] transition-all cursor-pointer flex items-center gap-1.5 {{ $statusFilter === $value ? 'bg-blue-900 text-white shadow-sm' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}">
                        <span>{{ $label }}</span>
                        <span class="text-[10px] font-medium {{ $statusFilter === $value ? 'text-blue-200' : 'text-neutral-400' }}">{{ $count }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Filter Tipe / Badge --}}
            @if ($availableBadges->isNotEmpty())
                <select wire:model.live="badgeFilter"
                        class="w-full sm:w-48 px-3.5 py-2.5 text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                    <option value="all">Semua Tipe</option>
                    @foreach ($availableBadges as $badgeOption)
                        <option value="{{ $badgeOption }}">{{ $badgeOption }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>

    {{-- Daftar Notifikasi --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="divide-y divide-neutral-100 dark:divide-slate-700">
            @forelse ($notifications as $notification)
                @php
                    $data       = $notification->data;
                    $isUnread   = is_null($notification->read_at);
                    $actionable = (bool) ($data['actionable'] ?? false);
                    $hasTarget  = \App\Support\NotificationLink::hasTarget($data);
                @endphp
                <div wire:key="notif-{{ $notification->id }}"
                     class="flex items-start gap-3 px-4 py-3.5 {{ $isUnread ? 'bg-blue-50/40 dark:bg-blue-950/10' : '' }} hover:bg-neutral-50/70 dark:hover:bg-slate-700/30 transition-colors">

                    {{-- Dot Status --}}
                    <span class="mt-1.5 h-2 w-2 rounded-full shrink-0 {{ $isUnread ? 'bg-blue-500' : 'bg-neutral-200 dark:bg-slate-600' }}"></span>

                    {{-- Konten --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-[13px] leading-snug {{ $isUnread ? 'font-bold text-neutral-900 dark:text-white' : 'font-semibold text-neutral-700 dark:text-neutral-300' }}">
                                {{ $data['title'] ?? '-' }}
                            </p>
                            @if (! empty($data['badge']))
                                <x-notifications.badge :label="$data['badge']" />
                            @endif
                        </div>
                        <p class="text-[12px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">{{ $data['message'] ?? '' }}</p>
                        <p class="text-[10px] text-neutral-400 dark:text-neutral-500 mt-1.5">
                            {{ $notification->created_at?->translatedFormat('d M Y, H:i') }}
                            <span class="mx-1">&middot;</span>
                            {{ $notification->created_at?->diffForHumans() }}
                        </p>

                        <x-notifications.actions :id="$notification->id" :actionable="$actionable" :has-target="$hasTarget" :unread="$isUnread" class="mt-2.5" />
                    </div>

                    {{-- Aksi Baris: Tandai Dibaca/Belum & Hapus --}}
                    <div class="flex items-center gap-1 shrink-0">
                        {{-- Yang butuh keputusan tidak bisa sekadar ditandai dibaca/belum. --}}
                        @unless ($actionable)
                            @if ($isUnread)
                                <button type="button" wire:click="markAsRead('{{ $notification->id }}')" title="Tandai sudah dibaca"
                                        class="p-1.5 text-neutral-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 rounded-md transition-all cursor-pointer">
                                    <x-heroicon-o-check class="w-4 h-4" stroke-width="2" />
                                </button>
                            @else
                                <button type="button" wire:click="markAsUnread('{{ $notification->id }}')" title="Tandai belum dibaca"
                                        class="p-1.5 text-neutral-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30 rounded-md transition-all cursor-pointer">
                                    <x-heroicon-o-envelope class="w-4 h-4" stroke-width="2" />
                                </button>
                            @endif
                        @endunless
                        <button type="button"
                                x-on:click.prevent="$store.confirmDialog.open({
                                    message: 'Hapus notifikasi ini secara permanen?',
                                    confirmText: 'Ya, Hapus',
                                    onConfirm: () => $wire.delete('{{ $notification->id }}')
                                })"
                                title="Hapus"
                                class="p-1.5 text-neutral-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-md transition-all cursor-pointer">
                            <x-heroicon-o-trash class="w-4 h-4" stroke-width="2" />
                        </button>
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 flex flex-col items-center text-center">
                    <span class="w-10 h-10 rounded-full bg-neutral-50 dark:bg-slate-900 border border-neutral-100 dark:border-slate-700 flex items-center justify-center text-neutral-400">
                        <x-heroicon-o-bell class="w-5 h-5" />
                    </span>
                    <p class="mt-3 text-xs text-neutral-400">
                        Tidak ada notifikasi{{ $statusFilter !== 'all' || $badgeFilter !== 'all' ? ' yang cocok dengan filter ini' : '' }}.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Footer Pagination --}}
        @if ($notifications->hasPages())
            <div class="px-4 py-3 border-t border-neutral-100 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-xs text-neutral-500 dark:text-neutral-400">
                    Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $notifications->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $notifications->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $notifications->total() }}</span> notifikasi
                </div>
                <div class="w-full md:w-auto flex justify-end">
                    {{ $notifications->links('components.custom-pagination') }}
                </div>
            </div>
        @endif
    </div>

    {{-- Popup kredensial baru -- muncul saat Admin Master menekan "Approve"
         pada permintaan reset password (lihat trait HandlesNotificationActions). --}}
    <x-credentials-modal :credentials="$createdCredentials" />

</div>

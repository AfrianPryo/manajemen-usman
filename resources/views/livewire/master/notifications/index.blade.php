<div class="w-full max-w-[1100px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="p-4 rounded-md bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('error') }}</span>
            <button @click="show = false" type="button" class="text-rose-500 hover:text-rose-700 dark:hover:text-rose-300">&times;</button>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

        {{-- Filter Status Baca --}}
        <div class="flex items-center bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-[3px] p-0.5 text-xs font-semibold shadow-sm">
            @foreach(['all' => 'Semua', 'unread' => 'Belum Dibaca', 'read' => 'Sudah Dibaca'] as $value => $label)
                <button
                    type="button"
                    wire:click="$set('statusFilter', '{{ $value }}')"
                    class="px-3 py-1.5 rounded-[3px] transition-all cursor-pointer {{ $statusFilter === $value ? 'bg-blue-900 text-white' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button
                wire:click="markAllAsRead"
                @if($unreadCount === 0) disabled @endif
                class="px-4 py-2 text-xs font-bold text-neutral-700 dark:text-neutral-200 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 hover:bg-neutral-50 dark:hover:bg-slate-700 rounded-[3px] transition-all flex items-center gap-2 shadow-sm disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Tandai Semua Dibaca</span>
            </button>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="flex flex-wrap items-center gap-2.5">
        {{-- Filter Tipe / Badge --}}
        @if($availableBadges->isNotEmpty())
            <select wire:model.live="badgeFilter" class="px-3 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-[3px] bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 shadow-sm cursor-pointer">
                <option value="all">Semua Tipe</option>
                @foreach($availableBadges as $badgeOption)
                    <option value="{{ $badgeOption }}">{{ $badgeOption }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Daftar Notifikasi --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="divide-y divide-neutral-100 dark:divide-slate-700">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $hasUrl = isset($data['url']) && $data['url'] !== '#';
                @endphp
                <div wire:key="notif-{{ $notification->id }}" class="flex items-start gap-3 px-4 py-3.5 {{ $isUnread ? 'bg-blue-50/40 dark:bg-blue-950/10' : '' }} hover:bg-neutral-50/70 dark:hover:bg-slate-700/30 transition-colors">

                    {{-- Dot Status --}}
                    <span class="mt-1.5 h-2 w-2 rounded-full shrink-0 {{ $isUnread ? 'bg-blue-500' : 'bg-neutral-200 dark:bg-slate-600' }}"></span>

                    {{-- Konten --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-[13px] {{ $isUnread ? 'font-bold text-neutral-900 dark:text-white' : 'font-semibold text-neutral-700 dark:text-neutral-300' }} truncate">
                                {{ $data['title'] ?? '-' }}
                            </p>
                            @if(isset($data['badge']))
                                <span class="text-[9px] px-1.5 py-0.5 rounded font-medium bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200 dark:border-amber-900 shrink-0 whitespace-nowrap">
                                    {{ $data['badge'] }}
                                </span>
                            @endif
                        </div>
                        <p class="text-[12px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                            {{ $data['message'] ?? '' }}
                        </p>
                        <p class="text-[10px] text-neutral-400 dark:text-neutral-500 mt-1.5">
                            {{ $notification->created_at?->translatedFormat('d M Y, H:i') }}
                            <span class="mx-1">&middot;</span>
                            {{ $notification->created_at?->diffForHumans() }}
                        </p>

                        {{-- Aksi Interaktif: Approve/Reject Transaksi Berulang --}}
                        @if(($data['actionable'] ?? false))
                            <div class="mt-2.5 flex items-center gap-2">
                                <button type="button"
                                        wire:click="approve('{{ $notification->id }}')"
                                        wire:loading.attr="disabled"
                                        class="px-2.5 py-1 text-[10px] font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded transition-colors cursor-pointer disabled:opacity-50">
                                    Approve
                                </button>
                                <button type="button"
                                        wire:click="reject('{{ $notification->id }}')"
                                        wire:loading.attr="disabled"
                                        class="px-2.5 py-1 text-[10px] font-semibold bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/80 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-400 rounded transition-colors cursor-pointer disabled:opacity-50">
                                    Reject
                                </button>
                                @if($hasUrl)
                                    <button type="button" wire:click="open('{{ $notification->id }}', '{{ $data['url'] }}')"
                                            class="ml-auto text-[10px] font-medium text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-200 transition-colors cursor-pointer">
                                        Detail &rarr;
                                    </button>
                                @endif
                            </div>
                        @elseif($hasUrl)
                            <button type="button" wire:click="open('{{ $notification->id }}', '{{ $data['url'] }}')"
                                    class="mt-2 text-[10px] font-medium text-blue-700 dark:text-blue-400 hover:underline transition-colors cursor-pointer">
                                Lihat Detail &rarr;
                            </button>
                        @endif
                    </div>

                    {{-- Aksi Baris: Tandai Dibaca/Belum & Hapus --}}
                    <div class="flex items-center gap-1 shrink-0">
                        @if($isUnread)
                            <button type="button" wire:click="markAsRead('{{ $notification->id }}')" title="Tandai sudah dibaca"
                                    class="p-1.5 text-neutral-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 rounded-md transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        @else
                            <button type="button" wire:click="markAsUnread('{{ $notification->id }}')" title="Tandai belum dibaca"
                                    class="p-1.5 text-neutral-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30 rounded-md transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="7"/></svg>
                            </button>
                        @endif
                        <button type="button"
                                x-on:click.prevent="$store.confirmDialog.open({
                                    message: 'Hapus notifikasi ini secara permanen?',
                                    confirmText: 'Ya, Hapus',
                                    onConfirm: () => $wire.delete('{{ $notification->id }}')
                                })"
                                title="Hapus"
                                class="p-1.5 text-neutral-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-md transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center text-xs text-neutral-400">
                    Tidak ada notifikasi{{ $statusFilter !== 'all' || $badgeFilter !== 'all' ? ' yang cocok dengan filter ini' : '' }}.
                </div>
            @endforelse
        </div>

        {{-- Footer Pagination --}}
        @if($notifications->hasPages())
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

    {{-- Popup kredensial baru (username/password) -- muncul saat Admin
         Master menekan "Approve" pada notifikasi permintaan reset password.
         Lihat komentar identik di resources/views/components/
         notification-sidebar.blade.php & App\Livewire\Master\Notifications\
         Index::approvePasswordResetRequest(). --}}
    <x-credentials-modal :credentials="$createdCredentials" />

</div>
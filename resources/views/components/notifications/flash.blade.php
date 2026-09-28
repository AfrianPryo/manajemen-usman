{{--
    Pemicu toast untuk pesan flash yang di-set oleh aksi notifikasi (lihat
    App\Support\Concerns\HandlesNotificationActions::toast()). Pola sama
    dengan halaman lain -- lihat komentar di components/alert.blade.php.
--}}
@foreach (['message' => 'success', 'error' => 'error'] as $flashKey => $toastType)
    @if (session()->has($flashKey))
        <div wire:key="notif-toast-{{ $flashKey }}-{{ md5(session($flashKey)) }}" x-data x-init="$store.toast.push('{{ $toastType }}', @js(session($flashKey)))"></div>
    @endif
@endforeach

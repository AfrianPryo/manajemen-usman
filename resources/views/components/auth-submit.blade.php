{{--
    Tombol submit halaman auth. Tombol berada di luar <form>, jadi terhubung lewat
    atribut form="...". "target" = nama method Livewire yang dipantau untuk state loading.

        <x-auth-submit form="loginForm" target="login" label="Buka Dashboard" />
--}}
@props(['form', 'target', 'label'])

<button
    form="{{ $form }}"
    type="submit"
    wire:loading.attr="disabled"
    class="flex w-full items-center justify-center gap-2 rounded-sm bg-blue-900 px-4 py-3 text-xs font-semibold text-white shadow-sm shadow-blue-900/20 transition-colors hover:bg-blue-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus-visible:ring-offset-slate-900"
>
    <svg wire:loading wire:target="{{ $target }}" class="h-3.5 w-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span wire:loading.remove wire:target="{{ $target }}">{{ $label }}</span>
    <span wire:loading wire:target="{{ $target }}">Memproses...</span>
</button>

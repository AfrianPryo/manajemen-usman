{{--
    Pesan error validasi di bawah field -- satu tampilan untuk semua form
    (ikon peringatan + teks merah), supaya seragam di seluruh aplikasi.

    Pemakaian (di dalam blok @error yang sudah ada):
        @error('email') <x-form-error :message="$message" :field="'email'" /> @enderror

    Atau langsung tanpa @error (pesan dibaca dari $errors):
        <x-form-error :field="'email'" />

    Props:
    - message : teks error (opsional bila field diisi)
    - field   : nama properti Livewire (sama dengan nilai wire:model). Dipakai
                resources/js/form-validation.js untuk memberi tanda merah pada
                kolom input yang bersangkutan, termasuk untuk field yang input-nya
                belum punya class error sendiri.
--}}
@props(['message' => null, 'field' => null])

@php
    $text = $message ?? ($field ? $errors->first($field) : null);
@endphp

@if ($text)
    <p {{ $attributes->merge(['class' => 'field-error mt-1 flex items-start gap-1 text-[11px] leading-snug font-medium text-rose-600 dark:text-rose-400']) }}
       role="alert"
       @if ($field) data-error-for="{{ $field }}" @endif>
        <svg class="mt-px size-3 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
        </svg>
        <span>{{ $text }}</span>
    </p>
@endif

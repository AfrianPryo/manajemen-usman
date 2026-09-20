{{-- Menampilkan flash message session('success') / session('error') secara otomatis di layout --}}
<div>
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="mb-4 rounded-lg bg-green-50 dark:bg-emerald-950/60 border border-green-200 dark:border-emerald-800 text-green-700 dark:text-emerald-300 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="mb-4 rounded-lg bg-red-50 dark:bg-rose-950/60 border border-red-200 dark:border-rose-800 text-blue-950 dark:text-rose-300 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif
</div>

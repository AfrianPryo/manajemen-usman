{{--
    Layout dasar halaman error HTTP (403, 404, 419, 429, 500, 503).
    Sengaja berdiri sendiri (tanpa Vite/Livewire/query database) supaya tetap
    bisa tampil walaupun penyebab error-nya ada di aset atau database.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') - @yield('title')</title>
    <style>
        :root { --bg:#f8fafc; --fg:#1e3a8a; --title:#374151; --muted:#6b7280; --btn:#2563eb; --btn-h:#1d4ed8; }
        @media (prefers-color-scheme: dark) {
            :root { --bg:#020617; --fg:#fb7185; --title:#e5e5e5; --muted:#a3a3a3; --btn:#1d4ed8; --btn-h:#2563eb; }
        }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
               background:var(--bg); font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif; }
        .box { text-align:center; padding:1.5rem; }
        .code { margin:0; font-size:3.75rem; font-weight:700; color:var(--fg); }
        .title { margin:1rem 0 0; font-size:1.25rem; color:var(--title); }
        .desc { margin:.5rem 0 0; color:var(--muted); max-width:28rem; }
        .btn { display:inline-block; margin-top:1.5rem; padding:.5rem 1rem; border-radius:.375rem;
               background:var(--btn); color:#fff; text-decoration:none; transition:background .15s; }
        .btn:hover { background:var(--btn-h); }
    </style>
</head>
<body>
    <div class="box">
        <h1 class="code">@yield('code')</h1>
        <p class="title">@yield('title')</p>
        <p class="desc">@yield('message')</p>
        <a href="{{ url('/') }}" class="btn">Kembali ke Beranda</a>
    </div>
</body>
</html>

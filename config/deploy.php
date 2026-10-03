<?php

return [
    /*
    | Token rahasia untuk route /deploy. Route HANYA aktif bila token diisi
    | (kosong = route mengembalikan 404). Isi lewat .env, contoh:
    |   DEPLOY_TOKEN=string-acak-panjang   (mis. `openssl rand -hex 32`)
    | Token dikirim lewat header "X-Deploy-Token" (disarankan) atau
    | "Authorization: Bearer <token>".
    */
    'token' => env('DEPLOY_TOKEN'),

    /*
    | Opsional: batasi akses ke daftar IP tertentu (pisahkan dengan koma),
    | mis. DEPLOY_ALLOWED_IPS=203.0.113.10,198.51.100.7
    | Kosong = tidak ada pembatasan IP (token tetap wajib).
    */
    'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('DEPLOY_ALLOWED_IPS', ''))))),
];

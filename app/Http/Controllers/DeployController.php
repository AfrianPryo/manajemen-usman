git <?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Pembungkus aman untuk skrip deploy.php di root proyek.
 *
 * Akses dibuka dengan salah satu cara:
 *  - Browser: buka /deploy, isi token di form, lalu sesi "terbuka" 15 menit.
 *  - curl/CI: header "X-Deploy-Token" atau "Authorization: Bearer <token>".
 *
 * Token berasal dari DEPLOY_TOKEN (config/deploy.php). Kosong = fitur mati (404).
 */
class DeployController extends Controller
{
    private const SESSION_KEY = 'deploy.unlocked_at';
    private const UNLOCK_MINUTES = 15;

    public function __invoke(Request $request)
    {
        $this->guard($request);

        $headerToken = (string) ($request->header('X-Deploy-Token') ?: $request->bearerToken());

        if (! $this->tokenValid($headerToken) && ! $this->sessionUnlocked($request)) {
            return $this->form();
        }

        return $this->run();
    }

    public function unlock(Request $request)
    {
        $this->guard($request);

        if (! $this->tokenValid((string) $request->input('token'))) {
            Log::warning('Deploy ditolak: token salah', ['ip' => $request->ip()]);

            return $this->form('Token salah.', 403);
        }

        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, now()->getTimestamp());

        return redirect()->route('deploy');
    }

    private function guard(Request $request): void
    {
        // Belum dikonfigurasi = fitur dimatikan (tidak membocorkan keberadaan route).
        abort_if((string) config('deploy.token') === '', 404);

        $allowedIps = (array) config('deploy.allowed_ips', []);
        if ($allowedIps !== [] && ! in_array($request->ip(), $allowedIps, true)) {
            Log::warning('Deploy ditolak: IP tidak diizinkan', ['ip' => $request->ip()]);
            abort(403);
        }
    }

    private function tokenValid(string $provided): bool
    {
        return $provided !== '' && hash_equals((string) config('deploy.token'), $provided);
    }

    private function sessionUnlocked(Request $request): bool
    {
        $at = $request->session()->get(self::SESSION_KEY);

        return is_int($at) && (now()->getTimestamp() - $at) < self::UNLOCK_MINUTES * 60;
    }

    private function run()
    {
        $script = base_path('deploy.php');
        abort_unless(is_file($script), 404);

        ob_start();
        try {
            require $script;
        } finally {
            $output = ob_get_clean();
        }

        // text/html: deploy.php boleh mengeluarkan tampilan HTML-nya sendiri.
        return response($output, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function form(?string $error = null, int $status = 200)
    {
        $csrf = csrf_token();
        $err = $error ? '<p style="color:#b91c1c;margin:0 0 12px">' . e($error) . '</p>' : '';
        $action = e(route('deploy.unlock'));

        $html = <<<HTML
<!doctype html><html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><title>Deploy</title></head>
<body style="font-family:system-ui,sans-serif;background:#f1f5f9;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0">
<form method="POST" action="{$action}" style="background:#fff;padding:24px;border-radius:12px;box-shadow:0 2px 12px #0002;width:320px">
<h2 style="margin:0 0 16px">Deploy</h2>{$err}
<input type="hidden" name="_token" value="{$csrf}">
<input type="password" name="token" placeholder="Token deploy" autocomplete="off" required autofocus
 style="width:100%;box-sizing:border-box;padding:10px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:12px">
<button type="submit" style="width:100%;padding:10px;border:0;border-radius:8px;background:#2563eb;color:#fff;font-weight:600;cursor:pointer">Buka</button>
</form></body></html>
HTML;

        return response($html, $status, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}

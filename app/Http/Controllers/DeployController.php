<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Pembungkus aman untuk skrip deploy.php di root proyek. Sebelumnya route
 * /deploy terbuka untuk publik; kini wajib token rahasia (config/deploy.php),
 * opsional whitelist IP, dan dibatasi throttle di routes/web.php.
 */
class DeployController extends Controller
{
    public function __invoke(Request $request)
    {
        $expected = (string) config('deploy.token');

        // Belum dikonfigurasi = fitur dimatikan (tidak membocorkan keberadaan route).
        abort_if($expected === '', 404);

        $allowedIps = (array) config('deploy.allowed_ips', []);
        if ($allowedIps !== [] && ! in_array($request->ip(), $allowedIps, true)) {
            Log::warning('Deploy ditolak: IP tidak diizinkan', ['ip' => $request->ip()]);
            abort(403);
        }

        $provided = (string) ($request->header('X-Deploy-Token') ?: $request->bearerToken());

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            Log::warning('Deploy ditolak: token salah', ['ip' => $request->ip()]);
            abort(403);
        }

        $script = base_path('deploy.php');
        abort_unless(is_file($script), 404);

        ob_start();
        try {
            require $script;
        } finally {
            $output = ob_get_clean();
        }

        return response($output, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}

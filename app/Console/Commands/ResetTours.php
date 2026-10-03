<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Mengosongkan status tutorial (users.completed_tours) supaya semua tutorial
 * muncul otomatis lagi di kunjungan berikutnya. Berguna saat pengujian.
 *
 *   php artisan tours:reset                 # semua akun
 *   php artisan tours:reset admin.master    # satu akun (username)
 */
class ResetTours extends Command
{
    protected $signature = 'tours:reset {username? : Username akun; kosongkan untuk semua akun} {--force : Wajib di production bila mereset SEMUA akun}';

    protected $description = 'Reset status tutorial (completed_tours) supaya tutorial tampil otomatis lagi';

    public function handle(): int
    {
        if (! $this->argument('username') && app()->isProduction() && ! $this->option('force')) {
            $this->error('Menolak mereset SEMUA akun di production tanpa --force. Beri username, atau tambahkan --force.');

            return self::FAILURE;
        }

        $query = User::withTrashed();

        if ($username = $this->argument('username')) {
            $query->where('username', $username);
        }

        $count = $query->update(['completed_tours' => null, 'onboarding_completed_at' => null]);

        $this->info("Status tutorial direset untuk {$count} akun.");

        return self::SUCCESS;
    }
}

<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\FonnteOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FonnteOtpServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pesan_dianggap_gagal_bila_fonnte_membalas_status_false(): void
    {
        Setting::set('wa_api_key', 'token-uji');
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'invalid token'], 200)]);

        $this->assertFalse(app(FonnteOtpService::class)->sendPlainMessage('08123456789', 'halo'));
    }

    public function test_pesan_dianggap_sukses_bila_fonnte_membalas_status_true(): void
    {
        Setting::set('wa_api_key', 'token-uji');
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true], 200)]);

        $this->assertTrue(app(FonnteOtpService::class)->sendPlainMessage('08123456789', 'halo'));
    }
}

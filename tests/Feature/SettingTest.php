<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_tidak_ikut_ter_cache(): void
    {
        $this->assertSame('A', Setting::get('kunci_belum_ada', 'A'));
        $this->assertSame('B', Setting::get('kunci_belum_ada', 'B'));

        Setting::set('kunci_belum_ada', 'nilai');
        $this->assertSame('nilai', Setting::get('kunci_belum_ada', 'B'));
    }

    public function test_token_fonnte_tersimpan_terenkripsi_tapi_terbaca_normal(): void
    {
        Setting::set('wa_api_key', 'rahasia-123');

        $this->assertNotSame('rahasia-123', DB::table('settings')->where('key', 'wa_api_key')->value('value'));
        $this->assertSame('rahasia-123', Setting::get('wa_api_key'));
    }
}

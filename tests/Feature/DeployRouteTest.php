<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeployRouteTest extends TestCase
{
    public function test_deploy_404_bila_token_belum_dikonfigurasi(): void
    {
        config(['deploy.token' => null]);

        $this->get('/deploy')->assertNotFound();
    }

    public function test_deploy_403_bila_token_salah(): void
    {
        config(['deploy.token' => 'benar']);

        $this->get('/deploy', ['X-Deploy-Token' => 'salah'])->assertForbidden();
        $this->get('/deploy')->assertForbidden();
    }
}

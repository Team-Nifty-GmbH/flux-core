<?php

namespace FluxErp\Tests\Route;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use FluxErp\Tests\Feature\BaseSetup;

class SettingsEmailsRouteTest extends BaseSetup
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->user, 'web');
    }

    public function test_dashboard_route_is_reachable()
    {
        $response = $this->get(route('settings.emails'));

        $response->assertStatus(200);
    }
}

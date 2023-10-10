<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Permission;
use FluxErp\Models\Setting;
use FluxErp\Models\User;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class SettingTest extends BaseSetup
{
    use DatabaseTransactions;

    private Setting $settings;

    private Setting $settings2;

    private Setting $userSettings;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settings = Setting::factory()->create([
            'key' => 'A' . Str::random(32),
            'settings' => [
                'string' => 'Key',
                'integer' => 1,
                'array' => [1, 2, 3],
            ],
        ]);
        $this->settings2 = Setting::factory()->create([
            'key' => 'Z' . Str::random(32),
            'settings' => [
                'settings' => 'settings',
            ],
        ]);

        $this->userSettings = Setting::factory()->create([
            'model_id' => $this->user->id,
            'model_type' => User::class,
            'settings' => [
                'profile' => 'bla',
                'value' => 12,
            ],
        ]);

        $this->permissions = [
            'index' => Permission::findOrCreate('api.settings.get'),
            'user-settings' => Permission::findOrCreate('api.user.settings.get'),
            'update' => Permission::findOrCreate('api.settings.put'),
        ];

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    public function test_get_settings()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/settings');
        $response->assertStatus(200);

        $responseData = json_decode($response->getContent(), true)['data']['data'];
        $count = count($responseData);
        $count -= 3;

        $this->assertEquals($this->settings->id, $responseData[$count]['id']);
        $this->assertEquals($this->settings->uuid, $responseData[$count]['uuid']);
        $this->assertEquals($this->settings->key, $responseData[$count]['key']);
        $this->assertEquals($this->settings->settings['string'], $responseData[$count]['settings']['string']);
        $this->assertEquals($this->settings->settings['integer'], $responseData[$count]['settings']['integer']);
        $this->assertEquals($this->settings->settings['array'], $responseData[$count]['settings']['array']);
        $this->assertEquals($this->settings2->id, $responseData[++$count]['id']);
        $this->assertEquals($this->settings2->uuid, $responseData[$count]['uuid']);
        $this->assertEquals($this->settings2->key, $responseData[$count]['key']);
        $this->assertEquals($this->settings2->settings, $responseData[$count]['settings']);
    }

    public function test_get_user_settings()
    {
        $this->user->givePermissionTo($this->permissions['user-settings']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/user/settings');
        $response->assertStatus(200);

        $responseData = json_decode($response->getContent())->data;
        $this->assertEquals(1, count($responseData));
        $this->assertEquals($this->userSettings->id, $responseData[0]->id);
        $this->assertEquals($this->userSettings->uuid, $responseData[0]->uuid);
        $this->assertEquals($this->userSettings->key, $responseData[0]->key);
        $this->assertEquals((object) $this->userSettings->settings, $responseData[0]->settings);
    }

    public function test_update_setting()
    {
        $settings = [
            'string' => 'Value',
            'integer' => 9,
            'array' => [9, 8, 6],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/settings', [
            'id' => $this->settings->id,
            'settings' => $settings,
        ]);
        $response->assertStatus(200);
        $setting = Setting::query()
            ->where('key', $this->settings->key)
            ->first();

        $this->assertEquals($this->settings->id, $setting->id);
        $this->assertEquals($this->settings->uuid, $setting->uuid);
        $this->assertEquals($this->settings->key, $setting->key);
        $this->assertEquals($settings['string'], $setting->settings['string']);
        $this->assertEquals($settings['integer'], $setting->settings['integer']);
        $this->assertEquals($settings['array'], $setting->settings['array']);
    }

    public function test_update_setting_validation_error()
    {
        $settings = 'string';

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/settings', [
            'id' => $this->settings->id,
            'settings' => $settings,
        ]);

        $response->assertStatus(422);
    }

    public function test_update_setting_setting_not_found()
    {
        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/settings', [
            'id' => ++$this->userSettings->id,
            'settings' => ['Value'],
        ]);

        $response->assertStatus(422);
    }
}

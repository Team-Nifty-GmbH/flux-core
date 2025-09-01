<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use FluxErp\Models\Permission;
use FluxErp\Models\Setting;
use FluxErp\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
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
        'model_type' => morph_alias(User::class),
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
});

test('get settings', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/settings');
    $response->assertStatus(200);

    $responseData = json_decode($response->getContent(), true)['data']['data'];
    $count = count($responseData);
    $count -= 3;

    expect($responseData[$count]['id'])->toEqual($this->settings->id);
    expect($responseData[$count]['uuid'])->toEqual($this->settings->uuid);
    expect($responseData[$count]['key'])->toEqual($this->settings->key);
    expect($responseData[$count]['settings']['string'])->toEqual($this->settings->settings['string']);
    expect($responseData[$count]['settings']['integer'])->toEqual($this->settings->settings['integer']);
    expect($responseData[$count]['settings']['array'])->toEqual($this->settings->settings['array']);
    expect($responseData[++$count]['id'])->toEqual($this->settings2->id);
    expect($responseData[$count]['uuid'])->toEqual($this->settings2->uuid);
    expect($responseData[$count]['key'])->toEqual($this->settings2->key);
    expect($responseData[$count]['settings'])->toEqual($this->settings2->settings);
});

test('get user settings', function (): void {
    $this->user->givePermissionTo($this->permissions['user-settings']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/user/settings');
    $response->assertStatus(200);

    $responseData = json_decode($response->getContent())->data;
    expect(count($responseData))->toEqual(1);
    expect($responseData[0]->id)->toEqual($this->userSettings->id);
    expect($responseData[0]->uuid)->toEqual($this->userSettings->uuid);
    expect($responseData[0]->key)->toEqual($this->userSettings->key);
    expect($responseData[0]->settings)->toEqual((object) $this->userSettings->settings);
});

test('update setting', function (): void {
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

    expect($setting->id)->toEqual($this->settings->id);
    expect($setting->uuid)->toEqual($this->settings->uuid);
    expect($setting->key)->toEqual($this->settings->key);
    expect($setting->settings['string'])->toEqual($settings['string']);
    expect($setting->settings['integer'])->toEqual($settings['integer']);
    expect($setting->settings['array'])->toEqual($settings['array']);
});

test('update setting setting not found', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/settings', [
        'id' => ++$this->userSettings->id,
        'settings' => ['Value'],
    ]);

    $response->assertStatus(422);
});

test('update setting validation error', function (): void {
    $settings = 'string';

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/settings', [
        'id' => $this->settings->id,
        'settings' => $settings,
    ]);

    $response->assertStatus(422);
});

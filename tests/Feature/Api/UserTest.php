<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use Carbon\Carbon;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->language = Language::factory()->create();

    $this->users = User::factory()->count(2)->create([
        'language_id' => $this->language->id,
    ]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.users.{id}.get'),
        'index' => Permission::findOrCreate('api.users.get'),
        'create' => Permission::findOrCreate('api.users.post'),
        'update' => Permission::findOrCreate('api.users.put'),
        'delete' => Permission::findOrCreate('api.users.{id}.delete'),
    ];
});

test('create user', function (): void {
    $user = User::factory()->make([
        'language_id' => $this->language->id,
        'password' => 'asdfdsfdsaf',
    ])->toArray();

    // validation requirement: min 8, mixedCase, numbers
    $user['password'] = 'Test12345';

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/users', $user);
    $response->assertStatus(201);

    $json = json_decode($response->getContent());
    $responseUser = $json->data;
    $dbUser = User::query()
        ->whereKey($responseUser->id)
        ->first();

    expect($user)->not->toBeEmpty();
    expect($dbUser->language_id)->toEqual($user['language_id']);
    expect($dbUser->email)->toEqual($user['email']);
    expect($dbUser->firstname)->toEqual($user['firstname']);
    expect($dbUser->lastname)->toEqual($user['lastname']);
    expect(Hash::check($user['password'], $dbUser->password))->toBeTrue();
    expect($dbUser->user_code)->toEqual($user['user_code']);
    expect($dbUser->is_active)->toEqual($user['is_active']);
    expect(property_exists($responseUser, 'password'))->toBeFalse();
});

test('create user validation fails', function (): void {
    $payload = [
        'language_id' => $this->language->id,
        'name' => Str::random(),
        'user_code' => Str::random(),
        'password' => '12345678',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/users', $payload);
    $response->assertStatus(422);
});

test('create user without language', function (): void {
    $user = User::factory()->make()->toArray();

    // validation requirement: min 8, mixedCase, numbers
    $user['password'] = 'Test12345';

    $this->language->is_default = true;
    $this->language->save();

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/users', $user);
    $response->assertStatus(201);

    $responseUser = json_decode($response->getContent())->data;
    $dbUser = User::query()
        ->whereKey($responseUser->id)
        ->first();

    $defaultLanguage = Language::default();

    expect($user)->not->toBeEmpty();
    expect($dbUser->firstname)->toEqual($user['firstname']);
    expect($dbUser->lastname)->toEqual($user['lastname']);
    expect($dbUser->name)->toEqual($user['firstname'] . ' ' . $user['lastname']);
    expect($dbUser->email)->toEqual($user['email']);
    expect(Hash::check($user['password'], $dbUser->password))->toBeTrue();
    expect($dbUser->user_code)->toEqual($user['user_code']);
    expect($dbUser->is_active)->toEqual($user['is_active']);
    expect(property_exists($responseUser, 'password'))->toBeFalse();
    expect($dbUser->language_id)->toEqual($defaultLanguage?->id);
});

test('delete user', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/users/' . $this->users[0]->id);
    $response->assertStatus(204);

    $dbUser = User::query()->withTrashed()->whereKey($this->users[0]->id)->first();
    expect($dbUser->deleted_at)->not->toBeNull();
});

test('delete user self', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/users/' . $this->user->id);
    $response->assertStatus(403);

    $dbUser = User::query()->whereKey($this->users[0]->id)->first();
    expect($dbUser)->not->toBeNull();
});

test('delete user user not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/users/' . $this->users[1]->id + 1);
    $response->assertStatus(404);
});

test('get user', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/users/' . $this->users[0]->id);
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonUser = $json->data;

    // Check if controller returns the test user.
    expect($jsonUser)->not->toBeEmpty();
    expect($jsonUser->id)->toEqual($this->users[0]->id);
    expect($jsonUser->language_id)->toEqual($this->users[0]->language_id);
    expect($jsonUser->email)->toEqual($this->users[0]->email);
    expect($jsonUser->firstname)->toEqual($this->users[0]->firstname);
    expect($jsonUser->lastname)->toEqual($this->users[0]->lastname);
    expect(property_exists($jsonUser, 'password'))->toBeFalse();
    expect($jsonUser->user_code)->toEqual($this->users[0]->user_code);
    expect($jsonUser->is_active)->toEqual($this->users[0]->is_active);
    expect(Carbon::parse($jsonUser->created_at))->toEqual(Carbon::parse($this->users[0]->created_at));
    expect(Carbon::parse($jsonUser->updated_at))->toEqual(Carbon::parse($this->users[0]->updated_at));
});

test('get user user not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/users/' . ++$this->users[1]->id);
    $response->assertStatus(404);
});

test('get users', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/users');
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonUsers = collect($json->data->data);

    // Check the amount of test users.
    expect(count($jsonUsers))->toBeGreaterThanOrEqual(2);

    // Check if controller returns the test users.
    foreach ($this->users as $user) {
        $jsonUsers->contains(function ($jsonUser) use ($user) {
            return $jsonUser->id === $user->id &&
                $jsonUser->language_id === $user->language_id &&
                $jsonUser->email === $user->email &&
                $jsonUser->firstname === $user->firstname &&
                $jsonUser->lastname === $user->lastname &&
                ! property_exists($jsonUser, 'password') &&
                $jsonUser->user_code === $user->user_code &&
                $jsonUser->is_active === $user->is_active &&
                Carbon::parse($jsonUser->created_at) === Carbon::parse($user->created_at) &&
                Carbon::parse($jsonUser->updated_at) === Carbon::parse($user->updated_at);
        });
    }
});

test('update user', function (): void {
    $user = [
        'id' => $this->users[0]->id,
        'email' => 'aRandomEmail@example.de',
        'password' => 'Asdf1234567',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/users', $user);
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $responseUser = $json->data;
    $dbUser = User::query()
        ->whereKey($responseUser->id)
        ->first();

    expect($dbUser)->not->toBeEmpty();
    expect($responseUser->id)->toEqual($user['id']);
    expect($dbUser->language_id)->toEqual($this->users[0]->language_id);
    expect($dbUser->email)->toEqual($user['email']);
    expect(Hash::check($user['password'], $dbUser->password))->toBeTrue();
    expect($dbUser->user_code)->toEqual($this->users[0]->user_code);
    expect($dbUser->is_active)->toEqual($this->users[0]->is_active);
    expect(property_exists($responseUser, 'password'))->toBeFalse();
});

test('update user is active', function (): void {
    $user = [
        'id' => $this->users[0]->id,
        'email' => 'random@mail-example.de',
        'password' => 'Asdf1234567',
        'is_active' => true,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/users', $user);
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $responseUser = $json->data;
    $dbUser = User::query()
        ->whereKey($responseUser->id)
        ->first();

    expect($dbUser)->not->toBeEmpty();
    expect($responseUser->id)->toEqual($user['id']);
    expect($dbUser->language_id)->toEqual($this->users[0]->language_id);
    expect($dbUser->email)->toEqual($user['email']);
    expect(Hash::check($user['password'], $dbUser->password))->toBeTrue();
    expect($dbUser->user_code)->toEqual($this->users[0]->user_code);
    expect($dbUser->is_active)->toEqual($user['is_active']);
    expect(property_exists($responseUser, 'password'))->toBeFalse();
});

test('update user validation fails', function (): void {
    $user = [
        'id' => $this->users[0]->id,
        'name' => 'a random name',
        'password' => 'asdf1234567',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/users', $user);
    $response->assertStatus(422);
});

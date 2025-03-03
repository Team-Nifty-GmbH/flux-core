<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class UserTest extends BaseSetup
{
    private Collection $users;

    private Language $language;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();
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
    }

    public function test_get_user()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/users/' . $this->users[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonUser = $json->data;

        // Check if controller returns the test user.
        $this->assertNotEmpty($jsonUser);
        $this->assertEquals($this->users[0]->id, $jsonUser->id);
        $this->assertEquals($this->users[0]->language_id, $jsonUser->language_id);
        $this->assertEquals($this->users[0]->email, $jsonUser->email);
        $this->assertEquals($this->users[0]->firstname, $jsonUser->firstname);
        $this->assertEquals($this->users[0]->lastname, $jsonUser->lastname);
        $this->assertFalse(property_exists($jsonUser, 'password'));
        $this->assertEquals($this->users[0]->user_code, $jsonUser->user_code);
        $this->assertEquals($this->users[0]->is_active, $jsonUser->is_active);
        $this->assertEquals(Carbon::parse($this->users[0]->created_at),
            Carbon::parse($jsonUser->created_at));
        $this->assertEquals(Carbon::parse($this->users[0]->updated_at),
            Carbon::parse($jsonUser->updated_at));
    }

    public function test_get_user_user_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/users/' . ++$this->users[1]->id);
        $response->assertStatus(404);
    }

    public function test_get_users()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/users');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonUsers = collect($json->data->data);

        // Check the amount of test users.
        $this->assertGreaterThanOrEqual(2, count($jsonUsers));

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
    }

    public function test_create_user()
    {
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

        $this->assertNotEmpty($user);
        $this->assertEquals($user['language_id'], $dbUser->language_id);
        $this->assertEquals($user['email'], $dbUser->email);
        $this->assertEquals($user['firstname'], $dbUser->firstname);
        $this->assertEquals($user['lastname'], $dbUser->lastname);
        $this->assertTrue(Hash::check($user['password'], $dbUser->password));
        $this->assertEquals($user['user_code'], $dbUser->user_code);
        $this->assertEquals($user['is_active'], $dbUser->is_active);
        $this->assertFalse(property_exists($responseUser, 'password'));
    }

    public function test_create_user_without_language()
    {
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

        $this->assertNotEmpty($user);
        $this->assertEquals($user['firstname'], $dbUser->firstname);
        $this->assertEquals($user['lastname'], $dbUser->lastname);
        $this->assertEquals($user['firstname'] . ' ' . $user['lastname'], $dbUser->name);
        $this->assertEquals($user['email'], $dbUser->email);
        $this->assertTrue(Hash::check($user['password'], $dbUser->password));
        $this->assertEquals($user['user_code'], $dbUser->user_code);
        $this->assertEquals($user['is_active'], $dbUser->is_active);
        $this->assertFalse(property_exists($responseUser, 'password'));
        $this->assertEquals($defaultLanguage?->id, $dbUser->language_id);
    }

    public function test_create_user_validation_fails()
    {
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
    }

    public function test_update_user()
    {
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

        $this->assertNotEmpty($dbUser);
        $this->assertEquals($user['id'], $responseUser->id);
        $this->assertEquals($this->users[0]->language_id, $dbUser->language_id);
        $this->assertEquals($user['email'], $dbUser->email);
        $this->assertTrue(Hash::check($user['password'], $dbUser->password));
        $this->assertEquals($this->users[0]->user_code, $dbUser->user_code);
        $this->assertEquals($this->users[0]->is_active, $dbUser->is_active);
        $this->assertFalse(property_exists($responseUser, 'password'));
    }

    public function test_update_user_is_active()
    {
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

        $this->assertNotEmpty($dbUser);
        $this->assertEquals($user['id'], $responseUser->id);
        $this->assertEquals($this->users[0]->language_id, $dbUser->language_id);
        $this->assertEquals($user['email'], $dbUser->email);
        $this->assertTrue(Hash::check($user['password'], $dbUser->password));
        $this->assertEquals($this->users[0]->user_code, $dbUser->user_code);
        $this->assertEquals($user['is_active'], $dbUser->is_active);
        $this->assertFalse(property_exists($responseUser, 'password'));
    }

    public function test_update_user_validation_fails()
    {
        $user = [
            'id' => $this->users[0]->id,
            'name' => 'a random name',
            'password' => 'asdf1234567',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/users', $user);
        $response->assertStatus(422);
    }

    public function test_delete_user()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/users/' . $this->users[0]->id);
        $response->assertStatus(204);

        $dbUser = User::query()->withTrashed()->whereKey($this->users[0]->id)->first();
        $this->assertNotNull($dbUser->deleted_at);
    }

    public function test_delete_user_self()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/users/' . $this->user->id);
        $response->assertStatus(422);

        $dbUser = User::query()->whereKey($this->users[0]->id)->first();
        $this->assertNotNull($dbUser);
    }

    public function test_delete_user_user_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/users/' . $this->users[1]->id + 1);
        $response->assertStatus(422);
    }
}

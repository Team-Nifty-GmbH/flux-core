<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class LockTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $users;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();
        $language = Language::factory()->create();

        $this->users = User::factory()->count(2)->create([
            'language_id' => $language->id,
        ]);

        $this->permissions = [
            'force_unlock' => Permission::findOrCreate('api.force-unlock.post'),
            'lock' => Permission::findOrCreate('api.lock.post'),
            'unlock' => Permission::findOrCreate('api.unlock.post'),
            'update_user' => Permission::findOrCreate('api.users.put'),
        ];
    }

    public function test_lock_record()
    {
        $this->user->givePermissionTo($this->permissions['lock']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->post('/api/lock', ['model_type' => User::class, 'model_id' => $this->users[0]->id]);
        $response->assertStatus(201);

        $json = json_decode($response->getContent());
        $responseLock = $json->data;

        $this->assertNotEmpty($responseLock);
        $this->assertEquals($this->users[0]->id, $responseLock->id);
    }

    public function test_unlock_record()
    {
        $this->user->givePermissionTo($this->permissions['unlock']);
        Sanctum::actingAs($this->user, ['user']);
        $this->users[0]->lock();

        $response = $this->actingAs($this->user)
            ->post('/api/unlock', ['model_type' => User::class, 'model_id' => $this->users[0]->id]);

        $response->assertStatus(204);
    }

    public function test_force_unlock_without_permission()
    {
        Sanctum::actingAs($this->user, ['user']);
        $this->users[0]->lock();

        $response = $this->actingAs($this->user)
            ->post('/api/force-unlock', ['model_type' => User::class, 'model_id' => $this->users[0]->id]);

        $response->assertStatus(403);
    }

    public function test_force_unlock()
    {
        $this->user->givePermissionTo($this->permissions['force_unlock']);
        Sanctum::actingAs($this->user, ['user']);
        $this->users[0]->lock();

        $response = $this->actingAs($this->user)
            ->post('/api/force-unlock', ['model_type' => User::class, 'model_id' => $this->users[0]->id]);

        $response->assertStatus(204);
    }

    public function test_update_locked_record()
    {
        $user = $this->users[0];
        $user->lock();

        $this->user->givePermissionTo($this->permissions['update_user']);
        Sanctum::actingAs($this->user, ['user']);
        $user->firstname = Str::random();

        $response = $this->actingAs($this->user)
            ->put('/api/users', $user->toArray());

        $response->assertStatus(423);
    }

    public function test_update_own_locked_record()
    {
        $this->user->givePermissionTo($this->permissions['update_user']);
        $this->user->givePermissionTo($this->permissions['lock']);

        Sanctum::actingAs($this->user, ['user']);
        Auth::guard('web')->login($this->user);
        $this->users[0]->lock();

        $this->users[0]->firstname = Str::random();
        $response = $this->actingAs($this->user)
            ->put('/api/users', $this->users[0]->toArray());

        $response->assertStatus(200);
    }
}

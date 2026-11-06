<?php

use FluxErp\Actions\Role\CreateRole;
use FluxErp\Actions\Role\UpdateRoleUsers;
use FluxErp\Actions\Role\UpdateUserRoles;
use FluxErp\Actions\User\CreateUser;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\Tag;
use FluxErp\Models\Token;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->contactPermission = Permission::findOrCreate('model.contact.get', 'web');
    $this->addressPermission = Permission::findOrCreate('model.address.get', 'web');
    $this->contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
});

test('search denies a user without the model permission', function (): void {
    $this->post(route('search', 'contact'), ['selected' => [$this->contact->getKey()]])
        ->assertForbidden();
});

test('search allows a user with the model permission', function (): void {
    $this->user->givePermissionTo($this->contactPermission);

    $this->post(route('search', 'contact'), ['selected' => [$this->contact->getKey()]])
        ->assertOk()
        ->assertJsonPath('0.id', $this->contact->getKey());
});

test('search allows a super admin without the model permission', function (): void {
    $this->user->assignRole(Role::findOrCreate('Super Admin', 'web'));

    $this->post(route('search', 'contact'), ['selected' => [$this->contact->getKey()]])
        ->assertOk();
});

test('search denies a relation the user may not read', function (array $parameters): void {
    $this->user->givePermissionTo($this->contactPermission);

    $this->post(route('search', 'contact'), $parameters)
        ->assertForbidden();
})->with([
    'searchFields' => [['search' => 'a', 'searchFields' => ['name', 'addresses.name']]],
    'with' => [['with' => ['addresses:id,name']]],
    'whereHas' => [['whereHas' => 'addresses']],
    'whereDoesntHave' => [['whereDoesntHave' => ['addresses']]],
    'whereRelation' => [['whereRelation' => ['addresses', 'name', 'like', '%a%']]],
    'whereDoesntHaveRelation' => [['whereDoesntHaveRelation' => ['addresses', 'name', 'x']]],
    'doesntHave' => [['doesntHave' => ['addresses']]],
    'nested with' => [['with' => ['mainAddress.contact']]],
    'with as nested array' => [['with' => ['addresses' => ['contact']]]],
    'with with constraint' => [['with' => ['addresses' => 'is_object']]],
    'fields' => [['selected' => [1], 'fields' => ['addresses']]],
    'appends' => [['appends' => ['addresses']]],
    'whereRelation with named arguments' => [[
        'whereRelation' => [
            'column' => 'name',
            'relation' => 'addresses',
            'operator' => 'like',
            'value' => '%a%',
        ],
    ]],
]);

test('search denies a morph to relation', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('model.comment.get', 'web'));

    $this->post(route('search', 'comment'), ['with' => ['model']])
        ->assertForbidden();
});

test('search does not call model methods that are no relation', function (): void {
    $this->user->givePermissionTo($this->contactPermission);
    $count = Contact::query()->count();

    $this->post(route('search', 'contact'), ['searchFields' => ['touch.name']])
        ->assertUnprocessable();

    expect(Contact::query()->count())->toBe($count);
});

test('search denies a model without model permissions', function (string $model): void {
    $this->post(route('search', $model), ['searchFields' => ['id'], 'search' => '1'])
        ->assertForbidden();
})->with(['activity', 'permission', 'role', 'media', 'notification']);

test('search allows tags without a model permission', function (): void {
    Tag::factory()->create(['type' => 'contact']);

    $this->post(route('search', 'tag'), ['searchFields' => ['name'], 'search' => ''])
        ->assertOk();
});

test('search allows media as a relation', function (): void {
    $this->user->givePermissionTo($this->contactPermission);

    $this->post(route('search', 'contact'), ['with' => 'media'])
        ->assertOk();
});

test('search denies activities as a relation', function (): void {
    $this->user->givePermissionTo($this->contactPermission);

    $this->post(route('search', 'contact'), ['with' => 'activitiesAsSubject'])
        ->assertForbidden();
});

test('search denies a model whose permission was never registered', function (): void {
    Permission::query()
        ->where('name', 'model.contact.get')
        ->delete();

    $this->post(route('search', 'contact'), ['selected' => [$this->contact->getKey()]])
        ->assertForbidden();
});

test('search denies an untyped relation fixed with a return type', function (): void {
    Permission::findOrCreate('model.job_batch.get', 'web');
    $this->user->givePermissionTo($this->addressPermission);

    $this->post(route('search', 'address'), ['with' => ['jobBatches']])
        ->assertForbidden();
});

test('search denies a relation method without a return type', function (string $model, string $relation): void {
    $this->user->givePermissionTo($this->addressPermission);

    $this->post(route('search', $model), ['with' => [$relation]])
        ->assertForbidden();
})->with([
    'notifications on address' => ['address', 'notifications'],
    'tokens on address' => ['address', 'tokens'],
    'notifications on user' => ['user', 'notifications'],
    'tokens on user' => ['user', 'tokens'],
]);

test('search checks a relation with a union return type', function (): void {
    $categoryPermission = Permission::findOrCreate('model.category.get', 'web');
    $this->user->givePermissionTo($this->contactPermission);

    $this->post(route('search', 'contact'), ['with' => ['category']])
        ->assertForbidden();

    $this->user->givePermissionTo($categoryPermission);

    $this->post(route('search', 'contact'), ['with' => ['category']])
        ->assertOk();
});

test('search allows a relation the user may read', function (): void {
    $this->user->givePermissionTo([$this->contactPermission, $this->addressPermission]);

    $this->post(route('search', 'contact'), ['whereHas' => 'addresses'])
        ->assertOk();
});

test('api search denies a user without the model permission', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('api.search.{model}.get', 'sanctum'));
    Sanctum::actingAs($this->user, ['user']);

    $this->getJson('/api/search/contact?selected[]=' . $this->contact->getKey())
        ->assertForbidden();
});

test('api search allows a user with the model permission', function (): void {
    $this->user->givePermissionTo([
        Permission::findOrCreate('api.search.{model}.get', 'sanctum'),
        $this->contactPermission,
    ]);
    Sanctum::actingAs($this->user, ['user']);

    $this->getJson('/api/search/contact?selected[]=' . $this->contact->getKey())
        ->assertOk();
});

test('api search denies a token without the model permission', function (): void {
    $token = Token::query()->create(['name' => 'integration']);
    $token->givePermissionTo(Permission::findOrCreate('api.search.{model}.get', 'token'));
    Permission::findOrCreate('model.contact.get', 'token');
    $plainTextToken = $token->createToken('integration', ['interface'])->plainTextToken;
    Auth::forgetGuards();

    $this->withToken($plainTextToken)
        ->getJson('/api/search/contact?selected[]=' . $this->contact->getKey())
        ->assertForbidden();
});

test('api search allows a token with the model permission', function (): void {
    $token = Token::query()->create(['name' => 'integration']);
    $token->givePermissionTo([
        Permission::findOrCreate('api.search.{model}.get', 'token'),
        Permission::findOrCreate('model.contact.get', 'token'),
    ]);
    $plainTextToken = $token->createToken('integration', ['interface'])->plainTextToken;
    Auth::forgetGuards();

    $this->withToken($plainTextToken)
        ->getJson('/api/search/contact?selected[]=' . $this->contact->getKey())
        ->assertOk();
});

test('a new role gets every model permission of its guard', function (): void {
    $role = CreateRole::make(['name' => 'Fresh role', 'guard_name' => 'web'])
        ->validate()
        ->execute();

    expect($role->hasPermissionTo($this->contactPermission))->toBeTrue()
        ->and($role->hasPermissionTo($this->addressPermission))->toBeTrue();
});

test('the migration grants every existing role the model permissions it lacks', function (): void {
    $role = Role::findOrCreate('Without model permissions', 'web');
    $role->givePermissionTo($this->contactPermission);

    (require __DIR__ . '/../../../database/migrations/2026_11_04_000001_grant_model_get_permissions.php')->up();

    expect(
        $role->fresh()
            ->permissions
            ->pluck('name')
            ->sort()
            ->values()
            ->all()
    )
        ->toBe(['model.address.get', 'model.contact.get']);
});

test('the migration grants users without a role the model permissions', function (): void {
    $withoutRole = User::factory()->create(['is_active' => true]);
    $withRole = User::factory()->create(['is_active' => true]);
    $withRole->assignRole(Role::findOrCreate('Some role', 'web'));

    (require __DIR__ . '/../../../database/migrations/2026_11_04_000001_grant_model_get_permissions.php')->up();

    expect($withoutRole->fresh()->hasDirectPermission('model.contact.get'))->toBeTrue()
        ->and($withRole->fresh()->hasDirectPermission('model.contact.get'))->toBeFalse()
        ->and($withRole->fresh()->hasPermissionTo('model.contact.get'))->toBeTrue();
});

test('a new user gets every model permission of its guard', function (): void {
    $user = CreateUser::make([
        'email' => 'fresh@example.com',
        'firstname' => 'Fresh',
        'lastname' => 'User',
        'user_code' => 'FRESH',
        'password' => 'Secret123!',
    ])
        ->validate()
        ->execute();

    expect($user->hasDirectPermission($this->contactPermission))->toBeTrue()
        ->and($user->hasDirectPermission($this->addressPermission))->toBeTrue();
});

test('a new user loses its direct model permissions once it gets a role', function (): void {
    $user = CreateUser::make([
        'email' => 'roled@example.com',
        'firstname' => 'Roled',
        'lastname' => 'User',
        'user_code' => 'ROLED',
        'password' => 'Secret123!',
    ])
        ->validate()
        ->execute();

    UpdateUserRoles::make([
        'user_id' => $user->getKey(),
        'roles' => [Role::findOrCreate('Without model permissions', 'web')->getKey()],
    ])
        ->validate()
        ->execute();

    expect($user->fresh()->hasDirectPermission($this->contactPermission))->toBeFalse()
        ->and($user->fresh()->hasPermissionTo($this->contactPermission))->toBeFalse();
});

test('users added to a role lose their direct model permissions', function (): void {
    $user = CreateUser::make([
        'email' => 'member@example.com',
        'firstname' => 'Member',
        'lastname' => 'User',
        'user_code' => 'MEMBER',
        'password' => 'Secret123!',
    ])
        ->validate()
        ->execute();
    $untouched = CreateUser::make([
        'email' => 'untouched@example.com',
        'firstname' => 'Untouched',
        'lastname' => 'User',
        'user_code' => 'UNTOUCHED',
        'password' => 'Secret123!',
    ])
        ->validate()
        ->execute();

    UpdateRoleUsers::make([
        'id' => Role::findOrCreate('Members', 'web')->getKey(),
        'users' => [$user->getKey()],
    ])
        ->validate()
        ->execute();

    expect($user->fresh()->hasDirectPermission($this->contactPermission))->toBeFalse()
        ->and($untouched->fresh()->hasDirectPermission($this->contactPermission))->toBeTrue();
});

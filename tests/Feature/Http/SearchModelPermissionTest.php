<?php

use FluxErp\Actions\Role\CreateRole;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
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
        ->assertOk();

    expect(Contact::query()->count())->toBe($count);
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

    (require __DIR__ . '/../../../database/migrations/2026_11_04_000001_grant_model_get_permissions_to_all_roles.php')->up();

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

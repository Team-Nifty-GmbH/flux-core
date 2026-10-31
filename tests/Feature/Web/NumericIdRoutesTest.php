<?php

use FluxErp\Models\Permission;
use Illuminate\Support\Facades\Route;

test('id route not numeric', function (string $routeName, string $uri): void {
    $this->user->givePermissionTo(
        Permission::findOrCreate(Route::getRoutes()->getByName($routeName)->getPermissionName(), 'web')
    );

    $this->actingAs($this->user, 'web')->get($uri)
        ->assertNotFound();
})->with([
    ['sales.lead.id', '/sales/leads/abc'],
    ['tasks.id', '/tasks/abc'],
    ['projects.id', '/projects/abc'],
    ['products.serial-numbers.id?', '/products/serial-numbers/abc'],
    ['human-resources.absence-requests.show', '/human-resources/absence-requests/abc'],
    ['human-resources.employee-days.show', '/human-resources/employee-days/abc'],
    ['human-resources.my-employee-profile.my-employee-day', '/human-resources/my-employee-profile/employee-day/abc'],
    ['human-resources.my-employee-profile.my-absence-request', '/human-resources/my-employee-profile/absence-request/abc'],
    ['settings.work-time-model', '/settings/work-time-model/abc'],
]);

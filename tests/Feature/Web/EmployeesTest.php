<?php

use FluxErp\Models\Permission;

test('employees id not numeric', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('employees.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/human-resources/employees/abc')
        ->assertNotFound();
});

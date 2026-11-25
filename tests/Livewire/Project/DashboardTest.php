<?php

use FluxErp\Livewire\Project\Dashboard;
use FluxErp\Livewire\Widgets\Project\TasksByState;
use FluxErp\Livewire\Widgets\ProjectMargin;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Dashboard::class)
        ->assertOk();
});

test('does not accept another project from the client', function (string $component): void {
    Livewire::test($component, ['projectId' => 1])
        ->set('projectId', 2);
})
    ->with([Dashboard::class, ProjectMargin::class, TasksByState::class])
    ->throws(CannotUpdateLockedPropertyException::class);

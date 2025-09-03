<?php

use FluxErp\Models\Client;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Route;
use Pest\Browser\Api\ArrayablePendingAwaitablePage;
use Pest\Browser\Api\PendingAwaitablePage;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(FluxErp\Tests\BrowserTestCase::class)->in('Browser');

/*
|--------------------------------------------------------------------------
| Browser Test Setup
|--------------------------------------------------------------------------
| Automatically setup required data and login for all browser tests
*/

uses()->beforeEach(function (): void {
    if (! $this->dbClient = Client::default()) {
        $this->dbClient = Client::factory()->create([
            'is_default' => true,
        ]);
    }

    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $this->actingAs($user);

    $this->user = $user;
})->in('Browser');

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/
function visitLivewire(string $component, array $options = []): ArrayablePendingAwaitablePage|PendingAwaitablePage
{
    Route::get($uri = '/livewire-test/' . uniqid(), $component);

    return visit($uri, $options);
}

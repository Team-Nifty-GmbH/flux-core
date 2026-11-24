<?php

use FluxErp\Livewire\Settings\Plugins;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Plugins::class)
        ->assertOk();
});

test('renders installed plugins when available plugins cannot be fetched', function (): void {
    $process = new Symfony\Component\Process\Process(['php', '-r', 'exit(1);']);
    $process->run();

    $composer = Mockery::mock(FluxErp\Helpers\Composer::class);
    $composer->shouldReceive('showAvailable')
        ->andThrow(new Symfony\Component\Process\Exception\ProcessFailedException($process));
    $composer->shouldReceive('installed')
        ->andReturn(['installed' => ['team-nifty-gmbh/flux-erp' => ['name' => 'team-nifty-gmbh/flux-erp']]]);
    app()->instance('composer', $composer);
    Illuminate\Support\Facades\Cache::forget('flux.plugins.available');

    Livewire::withoutLazyLoading()
        ->test(Plugins::class)
        ->assertOk()
        ->assertSet('installed', fn (array $installed): bool => array_key_exists('team-nifty-gmbh/flux-erp', $installed));
});

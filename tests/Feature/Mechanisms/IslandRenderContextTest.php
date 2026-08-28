<?php

use FluxErp\Tests\Fixtures\Livewire\IslandFixture;
use Livewire\Livewire;

test('an island render keeps bound components entangled', function (): void {
    $component = Livewire::test(IslandFixture::class)
        ->call('repaint');

    $lastState = (new ReflectionClass($component))->getProperty('lastState');
    $lastState->setAccessible(true);

    $fragments = data_get($lastState->getValue($component)->getEffects(), 'islandFragments', []);

    expect(implode('', $fragments))->toContain("\$wire.entangle('choice')");
});

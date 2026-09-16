<?php

use FluxErp\Tests\Fixtures\Livewire\IslandFixture;
use Livewire\Livewire;

function islandFragmentsOf(object $component): string
{
    $lastState = (new ReflectionClass($component))->getProperty('lastState');
    $lastState->setAccessible(true);

    return implode('', data_get($lastState->getValue($component)->getEffects(), 'islandFragments', []));
}

test('an island render keeps bound components entangled', function (): void {
    $component = Livewire::test(IslandFixture::class)
        ->call('repaint');

    expect(islandFragmentsOf($component))->toContain("\$wire.entangle('choice')");
});

test('a nested island render keeps bound components entangled', function (): void {
    $component = Livewire::test(IslandFixture::class)
        ->call('repaintNested');

    expect(islandFragmentsOf($component))->toContain("\$wire.entangle('nestedChoice')");
});

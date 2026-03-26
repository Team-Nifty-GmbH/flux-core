<?php

use FluxErp\Tests\Fixtures\Livewire\TabsFixture;
use FluxErp\Tests\Fixtures\Livewire\TabsFixtureChild;
use Livewire\Livewire;

test('tab livewire sub-component keeps identity after parent re-render', function (): void {
    Livewire::component('tabs-fixture', TabsFixture::class);
    Livewire::component('tabs-fixture-general', TabsFixtureChild::class);
    Livewire::component('tabs-fixture-child', TabsFixtureChild::class);

    $page = visitLivewire(TabsFixture::class)
        ->assertNoSmoke()
        ->assertSee('General');

    $page->script('() => new Promise(r => setTimeout(r, 1500))');

    $wireIdBefore = $page->script(<<<'JS'
        () => {
            const fixture = document.querySelector('[data-testid="tabs-fixture"]');
            const fixtureRoot = fixture.closest('[wire\\:id]');
            const fixtureRootId = fixtureRoot?.getAttribute('wire:id');
            const child = [...fixture.querySelectorAll('[wire\\:id]')]
                .find(el => el.getAttribute('wire:id') !== fixtureRootId);
            return child?.getAttribute('wire:id') ?? null;
        }
    JS);

    // Click refresh to trigger parent re-render and wait for Livewire response
    $page->click('Refresh');
    $page->script('() => new Promise(r => setTimeout(r, 2000))');

    $wireIdAfter = $page->script(<<<'JS'
        () => {
            const fixture = document.querySelector('[data-testid="tabs-fixture"]');
            const fixtureRoot = fixture.closest('[wire\\:id]');
            const fixtureRootId = fixtureRoot?.getAttribute('wire:id');
            const child = [...fixture.querySelectorAll('[wire\\:id]')]
                .find(el => el.getAttribute('wire:id') !== fixtureRootId);
            return child?.getAttribute('wire:id') ?? null;
        }
    JS);

    expect($wireIdBefore)->not->toBeNull('No Livewire sub-component found in tab');
    expect($wireIdAfter)->toBe($wireIdBefore,
        'Tab sub-component wire:id changed after parent re-render.'
    );
});

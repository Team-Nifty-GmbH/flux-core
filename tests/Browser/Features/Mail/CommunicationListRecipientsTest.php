<?php

use FluxErp\Livewire\DataTables\CommunicationList;
use FluxErp\Models\Communication;
use Illuminate\Support\Facades\Route;

test('to column shows each recipient once as name and address', function (): void {
    Communication::factory()->create([
        'communication_type_enum' => 'mail',
        'from' => 'Sender <sender@example.org>',
        'to' => [
            [
                'full' => 'Brenner Haustechnik GmbH <buero@brenner.example.org>',
                'host' => 'brenner.example.org',
                'mail' => 'buero@brenner.example.org',
                'mailbox' => 'buero',
                'personal' => 'Brenner Haustechnik GmbH',
            ],
        ],
    ]);

    Route::middleware('web')->get($uri = '/communication-list/' . uniqid(), CommunicationList::class);

    visit($uri)
        ->assertNoSmoke()
        ->assertSee('Brenner Haustechnik GmbH <buero@brenner.example.org>')
        ->assertScript(<<<'JS'
            (() => {
                const texts = Array.from(document.querySelectorAll('td span'))
                    .map(el => el.textContent.trim());

                return texts.filter(text => text.includes('buero@brenner.example.org')).length === 1
                    && ! texts.includes('brenner.example.org')
                    && ! texts.includes('buero');
            })()
        JS);
});

test('to column keeps plain address lists', function (): void {
    Communication::factory()->create([
        'communication_type_enum' => 'mail',
        'from' => 'sender@example.org',
        'to' => ['plain@example.org'],
    ]);

    Route::middleware('web')->get($uri = '/communication-list/' . uniqid(), CommunicationList::class);

    visit($uri)
        ->assertNoSmoke()
        ->assertSee('plain@example.org');
});

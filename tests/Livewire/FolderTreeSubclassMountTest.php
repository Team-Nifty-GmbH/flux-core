<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Tests\Fixtures\Livewire\FolderTreeWithOwnMount;
use Illuminate\Support\Facades\Process;
use Livewire\Livewire;

test('a subclass may declare its own mount signature', function (): void {
    // Separate process: an incompatible declaration is a compile error and would abort
    // the suite instead of failing here.
    $result = Process::path(dirname(__DIR__, 2))->run([
        PHP_BINARY,
        '-r',
        'require "vendor/autoload.php"; class_exists('
            . var_export(FolderTreeWithOwnMount::class, true)
            . ') && print "declared";',
    ]);

    expect($result->errorOutput())->not->toContain('must be compatible')
        ->and($result->output())->toContain('declared');
});

test('the media tree is filled even though the subclass brings its own mount', function (): void {
    $contact = Contact::factory()->create();
    Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $component = Livewire::test(FolderTreeWithOwnMount::class, [
        'address' => 'Berater',
        'contactId' => $contact->getKey(),
    ])
        ->assertOk();

    expect($component->get('address'))->toBe('Berater')
        ->and($component->get('mediaTree'))->not->toBeEmpty();
});

<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\NullEngine;

beforeEach(function (): void {
    // The collection engine reads the database instead of an index, so it can
    // not tell whether a model was handed to the engine at all.
    $this->engine = new class() extends NullEngine
    {
        public array $updated = [];

        public function update($models): void
        {
            foreach ($models as $model) {
                // Keyed by class, a contact and an address share the same ids.
                $this->updated[] = $model::class . ':' . $model->getKey();
            }
        }
    };

    $engine = $this->engine;

    app(EngineManager::class)->extend('spy', fn () => $engine);
    config()->set('scout.driver', 'spy');
});

test('a new main address hands its contact to the search index', function (): void {
    $contact = Contact::factory()->create();
    $this->engine->updated = [];

    Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'lastname' => 'Penkova',
    ]);

    expect($this->engine->updated)->toContain(Contact::class . ':' . $contact->getKey());
});

test('a renamed main address hands its contact to the search index', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'lastname' => 'Penkova',
    ]);
    $this->engine->updated = [];

    $address->update(['lastname' => 'Petrova']);

    expect($this->engine->updated)->toContain(Contact::class . ':' . $contact->getKey());
});

test('a secondary address leaves its contact alone', function (): void {
    $contact = Contact::factory()->create();
    Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);
    $this->engine->updated = [];

    Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => false,
        'is_invoice_address' => false,
        'is_delivery_address' => false,
    ]);

    expect($this->engine->updated)->not->toContain(Contact::class . ':' . $contact->getKey());
});

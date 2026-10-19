<?php

use FluxErp\Actions\Record\MergeRecords;
use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use Illuminate\Support\Facades\DB;

test('merges addresses when categorizable_id coincides with another model id', function (): void {
    $contact = Contact::factory()->create();

    $mainAddress = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_main_address' => false,
    ]);

    $mergeAddress = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_main_address' => false,
    ]);

    $category = Category::factory()->create([
        'model_type' => morph_alias(Contact::class),
    ]);

    // Categorizable entry matching the merge address ID triggers NOT NULL violation on categorizable_type.
    DB::table('categorizable')->insert([
        'category_id' => $category->getKey(),
        'categorizable_type' => morph_alias(Contact::class),
        'categorizable_id' => $mergeAddress->getKey(),
    ]);

    $result = MergeRecords::make([
        'model_type' => morph_alias(Address::class),
        'main_record' => [
            'id' => $mainAddress->getKey(),
            'columns' => [],
        ],
        'merge_records' => [
            [
                'id' => $mergeAddress->getKey(),
                'columns' => [],
            ],
        ],
    ])
        ->checkPermission()
        ->validate()
        ->execute();

    expect($result->getKey())->toBe($mainAddress->getKey());
    expect(Address::query()->whereKey($mergeAddress->getKey())->first())->toBeNull();
});

test('merging contacts keeps exactly one main, invoice and delivery address', function (): void {
    $target = Contact::factory()->create();
    $targetMain = Address::factory()->create([
        'contact_id' => $target->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);
    $target->update([
        'main_address_id' => $targetMain->getKey(),
        'invoice_address_id' => $targetMain->getKey(),
        'delivery_address_id' => $targetMain->getKey(),
    ]);

    $source = Contact::factory()->create();
    $sourceMain = Address::factory()->create([
        'contact_id' => $source->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);
    Address::factory()->count(2)->create([
        'contact_id' => $source->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_main_address' => false,
        'is_invoice_address' => false,
        'is_delivery_address' => false,
    ]);
    $source->update([
        'main_address_id' => $sourceMain->getKey(),
        'invoice_address_id' => $sourceMain->getKey(),
        'delivery_address_id' => $sourceMain->getKey(),
    ]);

    MergeRecords::make([
        'model_type' => morph_alias(Contact::class),
        'main_record' => [
            'id' => $target->getKey(),
            'columns' => [],
        ],
        'merge_records' => [
            [
                'id' => $source->getKey(),
                'columns' => [],
            ],
        ],
    ])
        ->checkPermission()
        ->validate()
        ->execute();

    $target->refresh();
    $addresses = Address::query()->where('contact_id', $target->getKey())->get();

    expect($addresses)->toHaveCount(4)
        ->and($addresses->where('is_main_address', true)->pluck('id')->all())
        ->toBe([$target->main_address_id])
        ->and($addresses->where('is_invoice_address', true)->pluck('id')->all())
        ->toBe([$target->invoice_address_id])
        ->and($addresses->where('is_delivery_address', true)->pluck('id')->all())
        ->toBe([$target->delivery_address_id]);
});

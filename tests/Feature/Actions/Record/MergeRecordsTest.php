<?php

use FluxErp\Actions\Record\MergeRecords;
use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use Illuminate\Support\Facades\DB;

test('merges addresses when categorizable_id coincides with another model id', function (): void {
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $mainAddress = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_main_address' => false,
    ]);

    $mergeAddress = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
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

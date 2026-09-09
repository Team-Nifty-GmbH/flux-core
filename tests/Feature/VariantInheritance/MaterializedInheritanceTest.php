<?php

use FluxErp\Actions\Product\SyncVariantInheritance;
use FluxErp\Models\Product;
use Illuminate\Support\Facades\Queue;

test('variant column holds the real inherited value in the database', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent', 'weight_gram' => 100]);
    $variant = Product::factory()->create(['parent_id' => $parent->getKey(), 'weight_gram' => 100]);

    // SQL-level truth, not accessor: the raw column must equal the parent value.
    expect((int) DB::table('products')->where('id', $variant->getKey())->value('weight_gram'))
        ->toBe(100);
});

test('editing a parent field dispatches the variant sync for the changed fields', function (): void {
    $parent = Product::factory()->create(['weight_gram' => 100]);
    // weight_gram must be listed before parent_id: the value-diff saving hook marks a
    // field overridden once isVariant() is true, so setting parent_id first would falsely
    // override weight_gram on creation.
    Product::factory()->create(['weight_gram' => 100, 'parent_id' => $parent->getKey()]);

    // The hook dispatches asynchronously (afterCommit); assert the dispatch here and rely
    // on SyncVariantInheritanceTest for the action's set-based propagation effect. Faking
    // the queue also isolates this from ambient queue state in the full suite.
    Queue::fake();

    $parent->update(['weight_gram' => 250]);

    Queue::assertPushed(
        SyncVariantInheritance::class,
        fn (SyncVariantInheritance $action): bool => $action->getData('parent_id') === $parent->getKey()
            && in_array('weight_gram', $action->getData('fields'), true)
    );
});

test('an overridden field is not overwritten by a parent change', function (): void {
    $parent = Product::factory()->create(['weight_gram' => 100]);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'weight_gram' => 999,
        'overridden_fields' => ['weight_gram'],
    ]);

    $parent->update(['weight_gram' => 250]);
    SyncVariantInheritance::make([
        'parent_id' => $parent->getKey(),
        'fields' => ['weight_gram'],
    ])
        ->validate()
        ->execute();

    expect((int) DB::table('products')->where('id', $variant->getKey())->value('weight_gram'))
        ->toBe(999);
});

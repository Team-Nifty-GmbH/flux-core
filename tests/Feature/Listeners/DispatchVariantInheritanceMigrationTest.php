<?php

use FluxErp\Jobs\MigrateProductVariantInheritanceJob;
use FluxErp\Settings\ProductSettings;
use Illuminate\Support\Facades\Queue;

test('enabling variant inheritance dispatches the migration job', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    Queue::fake();

    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    Queue::assertPushed(MigrateProductVariantInheritanceJob::class);
});

test('saving the settings without enabling does not dispatch the migration job', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    Queue::fake();

    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    Queue::assertNothingPushed();
});

test('re-saving with the feature already enabled does not dispatch the migration job', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    Queue::fake();

    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    Queue::assertNothingPushed();
});

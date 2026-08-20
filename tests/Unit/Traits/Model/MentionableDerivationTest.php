<?php

use FluxErp\Tests\Fixtures\DataTableMentionFixture;
use FluxErp\Tests\Fixtures\MentionableFixture;

test('derives the mention label and url from InteractsWithDataTables', function (): void {
    $model = new DataTableMentionFixture(['label_value' => 'from-label', 'url_value' => '/from-url']);

    expect($model->getMentionLabel())->toBe('from-label');
    expect($model->getMentionUrl())->toBe('/from-url');
});

test('returns a null mention url when getUrl is null', function (): void {
    $model = new DataTableMentionFixture(['label_value' => 'x']);

    expect($model->getMentionUrl())->toBeNull();
});

test('falls back to the keyed label for a trait-only model without getLabel', function (): void {
    $model = (new MentionableFixture())->forceFill(['id' => 7]);

    expect($model->getMentionLabel())->toBe('#7');
});

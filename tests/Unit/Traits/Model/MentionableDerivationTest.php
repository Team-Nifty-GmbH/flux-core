<?php

use FluxErp\Tests\Fixtures\DataTableMentionFixture;
use FluxErp\Tests\Fixtures\MentionableFixture;

it('derives the mention label and url from InteractsWithDataTables', function (): void {
    $model = new DataTableMentionFixture(['label_value' => 'from-label', 'url_value' => '/from-url']);

    expect($model->getMentionLabel())->toBe('from-label');
    expect($model->getMentionUrl())->toBe('/from-url');
});

it('returns a null mention url when getUrl is null', function (): void {
    $model = new DataTableMentionFixture(['label_value' => 'x']);

    expect($model->getMentionUrl())->toBeNull();
});

it('uses the name fallback label for a trait-only model without InteractsWithDataTables', function (): void {
    $model = new MentionableFixture(['name' => 'Generic Name']);

    expect($model->getMentionLabel())->toBe('Generic Name');
});

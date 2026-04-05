<?php

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\UpdateMedia;

test('delete media requires id', function (): void {
    DeleteMedia::assertValidationErrors([], 'id');
});

test('update media requires id', function (): void {
    UpdateMedia::assertValidationErrors([], 'id');
});

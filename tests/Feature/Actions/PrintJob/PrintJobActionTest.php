<?php

use FluxErp\Actions\PrintJob\CreatePrintJob;

test('create print job requires printer_id and media_id', function (): void {
    CreatePrintJob::assertValidationErrors([], ['printer_id', 'media_id']);
});

<?php

use FluxErp\Actions\ActionManager;
use FluxErp\Actions\PurchaseInvoice\UploadPurchaseInvoice;
use FluxErp\Contracts\HandlesSharedFiles;

test('is discoverable through the action manager as a shared file handler', function (): void {
    $sharedFileActions = app(ActionManager::class)
        ->all()
        ->pluck('class')
        ->filter(fn (string $action): bool => is_a($action, HandlesSharedFiles::class, true));

    expect($sharedFileActions)->toContain(UploadPurchaseInvoice::class);
});

test('accepts invoice mime types only', function (): void {
    expect(UploadPurchaseInvoice::accepts('application/pdf'))->toBeTrue()
        ->and(UploadPurchaseInvoice::accepts('image/jpeg'))->toBeTrue()
        ->and(UploadPurchaseInvoice::accepts('application/xml'))->toBeTrue()
        ->and(UploadPurchaseInvoice::accepts('video/mp4'))->toBeFalse()
        ->and(UploadPurchaseInvoice::accepts(null))->toBeFalse();
});

<?php

use FluxErp\Actions\ActionManager;
use FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice;
use FluxErp\Contracts\HandlesSharedFiles;

test('is discoverable through the action manager as a shared file handler', function (): void {
    $sharedFileActions = app(ActionManager::class)
        ->all()
        ->pluck('class')
        ->filter(fn (string $action): bool => is_a($action, HandlesSharedFiles::class, true));

    expect($sharedFileActions)->toContain(CreatePurchaseInvoice::class);
});

test('accepts invoice mime types only', function (): void {
    expect(CreatePurchaseInvoice::accepts('application/pdf'))->toBeTrue()
        ->and(CreatePurchaseInvoice::accepts('image/jpeg'))->toBeTrue()
        ->and(CreatePurchaseInvoice::accepts('application/xml'))->toBeTrue()
        ->and(CreatePurchaseInvoice::accepts('video/mp4'))->toBeFalse()
        ->and(CreatePurchaseInvoice::accepts(null))->toBeFalse();
});

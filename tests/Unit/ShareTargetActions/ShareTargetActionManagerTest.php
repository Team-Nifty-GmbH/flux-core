<?php

use FluxErp\ShareTargetActions\ShareTargetActionManager;
use FluxErp\ShareTargetActions\UploadPurchaseInvoice;

test('registers share target actions', function (): void {
    $manager = new ShareTargetActionManager();
    $manager->register(UploadPurchaseInvoice::class);

    expect($manager->all())->toBe([UploadPurchaseInvoice::class])
        ->and($manager->has(UploadPurchaseInvoice::class))->toBeTrue();
});

test('registering the same action twice keeps it once', function (): void {
    $manager = new ShareTargetActionManager();
    $manager->register(UploadPurchaseInvoice::class);
    $manager->register(UploadPurchaseInvoice::class);

    expect($manager->all())->toHaveCount(1);
});

test('rejects classes that do not extend the base action', function (): void {
    $manager = new ShareTargetActionManager();

    expect(fn () => $manager->register(stdClass::class))
        ->toThrow(InvalidArgumentException::class);
});

test('upload purchase invoice action accepts invoice mime types only', function (): void {
    expect(UploadPurchaseInvoice::accepts('application/pdf'))->toBeTrue()
        ->and(UploadPurchaseInvoice::accepts('image/jpeg'))->toBeTrue()
        ->and(UploadPurchaseInvoice::accepts('application/xml'))->toBeTrue()
        ->and(UploadPurchaseInvoice::accepts('video/mp4'))->toBeFalse()
        ->and(UploadPurchaseInvoice::accepts(null))->toBeFalse();
});

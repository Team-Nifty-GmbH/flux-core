<?php

use FluxErp\Actions\PurchaseInvoice\UploadPurchaseInvoice;
use FluxErp\Livewire\Mobile\ShareTarget;
use FluxErp\Models\Permission;
use FluxErp\Models\PurchaseInvoice;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

function fakeSharedInvoice(string $name = 'invoice.jpeg', int $size = 10): UploadedFile
{
    return UploadedFile::fake()->image($name, $size, $size);
}

test('renders successfully', function (): void {
    Livewire::test(ShareTarget::class)
        ->assertOk();
});

test('upload purchase invoice action is registered and enabled for shared files', function (): void {
    $component = Livewire::test(ShareTarget::class)
        ->set('files', [fakeSharedInvoice()])
        ->assertOk();

    $actions = collect($component->instance()->actions());

    expect($actions->pluck('class'))->toContain(UploadPurchaseInvoice::class)
        ->and($actions->firstWhere('class', UploadPurchaseInvoice::class)['enabled'])->toBeTrue();
});

test('upload purchase invoice action creates one purchase invoice per file', function (): void {
    Livewire::test(ShareTarget::class)
        ->set('files', [fakeSharedInvoice('invoice-1.jpeg', 10), fakeSharedInvoice('invoice-2.jpeg', 20)])
        ->call('executeAction', UploadPurchaseInvoice::class)
        ->assertOk()
        ->assertDispatched('share-target-completed');

    $purchaseInvoices = resolve_static(PurchaseInvoice::class, 'query')->get();

    expect($purchaseInvoices)->toHaveCount(2)
        ->and($purchaseInvoices->first()->media_id)->not->toBeNull()
        ->and($purchaseInvoices->first()->getFirstMedia('purchase_invoice'))->not->toBeNull();
});

test('action is hidden when the user lacks its permission', function (): void {
    Permission::findOrCreate('action.' . UploadPurchaseInvoice::name(), 'web');

    $component = Livewire::test(ShareTarget::class)
        ->set('files', [fakeSharedInvoice()]);

    expect(collect($component->instance()->actions())->pluck('class'))
        ->not->toContain(UploadPurchaseInvoice::class);
});

test('action is shown when the user has its permission', function (): void {
    $permission = Permission::findOrCreate('action.' . UploadPurchaseInvoice::name(), 'web');
    $this->user->givePermissionTo($permission);

    $component = Livewire::test(ShareTarget::class)
        ->set('files', [fakeSharedInvoice()]);

    expect(collect($component->instance()->actions())->pluck('class'))
        ->toContain(UploadPurchaseInvoice::class);
});

test('unregistered action is rejected', function (): void {
    Livewire::test(ShareTarget::class)
        ->set('files', [fakeSharedInvoice()])
        ->call('executeAction', stdClass::class)
        ->assertOk()
        ->assertNotDispatched('share-target-completed');

    expect(resolve_static(PurchaseInvoice::class, 'query')->count())->toBe(0);
});

test('action without files is rejected', function (): void {
    Livewire::test(ShareTarget::class)
        ->call('executeAction', UploadPurchaseInvoice::class)
        ->assertOk()
        ->assertNotDispatched('share-target-completed');

    expect(resolve_static(PurchaseInvoice::class, 'query')->count())->toBe(0);
});

test('duplicate files still complete with partial success', function (): void {
    Livewire::test(ShareTarget::class)
        ->set('files', [fakeSharedInvoice('invoice-1.jpeg'), fakeSharedInvoice('invoice-copy.jpeg')])
        ->call('executeAction', UploadPurchaseInvoice::class)
        ->assertOk()
        ->assertDispatched('share-target-completed');

    expect(resolve_static(PurchaseInvoice::class, 'query')->count())->toBe(1);
});

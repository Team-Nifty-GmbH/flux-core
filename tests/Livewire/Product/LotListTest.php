<?php

use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Livewire\Product\LotList;
use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->product = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create(['is_lot_tracked' => true]);

    $this->productForm = function (): ProductForm {
        $form = new ProductForm(Livewire::new(LotList::class), 'product');
        $form->fill($this->product);

        return $form;
    };
});

test('renders successfully', function (): void {
    Livewire::test(LotList::class, ['product' => ($this->productForm)()])
        ->assertOk();
});

test('lists only lots of the given product', function (): void {
    $ownLot = Lot::factory()->create(['product_id' => $this->product->getKey()]);
    $otherLot = Lot::factory()->create([
        'product_id' => Product::factory()->create()->getKey(),
    ]);

    $rows = Livewire::test(LotList::class, ['product' => ($this->productForm)()])
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting()['data'];

    expect(array_column($rows, 'id'))->toBe([$ownLot->getKey()])
        ->and(array_column($rows, 'id'))->not->toContain($otherLot->getKey());
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(LotList::class, ['product' => ($this->productForm)()])
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('lot.id', null)
        ->assertSet('lot.lot_number', null)
        ->assertOpensModal('edit-lot-modal');
});

test('can create lot for the product', function (): void {
    $lotNumber = Str::uuid()->toString();

    Livewire::test(LotList::class, ['product' => ($this->productForm)()])
        ->call('edit')
        ->set('lot.lot_number', $lotNumber)
        ->set('lot.expires_at', '2027-01-31')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('lots', [
        'lot_number' => $lotNumber,
        'product_id' => $this->product->getKey(),
    ]);
});

test('can update lot', function (): void {
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    Livewire::test(LotList::class, ['product' => ($this->productForm)()])
        ->call('edit', $lot->getKey())
        ->set('lot.supplier_lot_number', 'SUP-4711')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($lot->refresh()->supplier_lot_number)->toEqual('SUP-4711');
});

test('can delete lot', function (): void {
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    Livewire::test(LotList::class, ['product' => ($this->productForm)()])
        ->call('delete', $lot->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertSoftDeleted('lots', ['id' => $lot->getKey()]);
});

test('save fails without lot number', function (): void {
    Livewire::test(LotList::class, ['product' => ($this->productForm)()])
        ->call('edit')
        ->set('lot.lot_number', null)
        ->call('save')
        ->assertOk()
        ->assertHasErrors(['lot.lot_number'])
        ->assertReturned(false);
});

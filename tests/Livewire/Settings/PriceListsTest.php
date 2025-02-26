<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Forms\PriceListForm;
use FluxErp\Livewire\Settings\PriceLists;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Str;
use Livewire\Livewire;

class PriceListsTest extends BaseSetup
{
    private PriceList $priceList;

    protected function setUp(): void
    {
        parent::setUp();
        $this->priceList = PriceList::factory()->create();
    }

    public function test_renders_successfully()
    {
        Livewire::test(PriceLists::class)
            ->assertStatus(200);
    }

    public function test_can_create_price_list()
    {
        Livewire::test(PriceLists::class)
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->set('priceList.name', $name = Str::uuid())
            ->set('priceList.price_list_code', Str::uuid())
            ->call('save')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('price_lists', [
            'name' => $name,
        ]);
    }

    public function test_can_update_price_list()
    {
        $child = PriceList::factory()
            ->create([
                'parent_id' => $this->priceList->id,
            ]);
        $form = new PriceListForm(Livewire::new(PriceLists::class), 'contact');
        $form->fill($child);

        Livewire::test(PriceLists::class, ['priceList' => $form])
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->set('priceList.name', 'New Name')
            ->set(
                'priceList.discount',
                [
                    'discount' => 10,
                    'is_percentage' => true,
                ]
            )
            ->call('save')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $dbPriceList = $child->refresh();

        $this->assertEquals('New Name', $dbPriceList->name);
        $this->assertEquals(0.1, $dbPriceList->discount->discount);
        $this->assertTrue($dbPriceList->discount->is_percentage);
    }
}

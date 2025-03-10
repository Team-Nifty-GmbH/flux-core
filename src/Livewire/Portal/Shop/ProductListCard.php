<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Actions\CartItem\UpdateCartItem;
use FluxErp\Livewire\Forms\Portal\ProductForm;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class ProductListCard extends Component
{
    public ProductForm $productForm;

    public ?int $cartItemId = null;

    public function mount(array $product): void
    {
        $this->productForm->fill($product);
    }

    public function render(): View
    {
        return view('flux::livewire.portal.shop.product-list-card');
    }

    public function placeholder(): string
    {
        return <<<'Blade'
            <div>
                <x-card class="flex flex-col justify-between gap-1.5 z-0">
                    <div class="h-1/2 w-full overflow-hidden rounded-md bg-gray-200 group-hover:opacity-75 lg:h-72 xl:h-80 relative flex justify-items-center">
                        @include('flux::livewire.placeholders.box')
                    </div>
                    @include('flux::livewire.placeholders.horizontal-bar')
                </x-card>
            </div>
        Blade;
    }

    public function updatedProductFormAmount(): void
    {
        try {
            UpdateCartItem::make([
                'id' => $this->cartItemId,
                'amount' => $this->productForm->amount,
            ])->validate()->execute();

            $this->skipRender();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }
    }
}

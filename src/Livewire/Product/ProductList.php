<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\DataTables\ProductList as BaseProductList;
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Models\Client;
use FluxErp\Models\VatRate;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportRedirects\Redirector;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ProductList extends BaseProductList
{
    protected string $view = 'flux::livewire.product.product-list';

    public ?string $cacheKey = 'product.product-list';

    public ProductForm $product;

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('New'))
                ->icon('plus')
                ->attributes([
                    'x-on:click' => "\$openModal('create-product')",
                ]),
        ];
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'clients' => app(Client::class)->query()
                    ->get(['id', 'name'])
                    ->toArray(),
                'vatRates' => app(VatRate::class)->query()
                    ->get(['id', 'name', 'rate_percentage'])
                    ->toArray(),
            ]
        );
    }

    public function save(): false|Redirector
    {
        try {
            $this->product->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return redirect()->to(route('products.id', $this->product->id));
    }
}

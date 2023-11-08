<?php

namespace FluxErp\Livewire\Product\SerialNumber;

use FluxErp\Actions\SerialNumber\CreateSerialNumber;
use FluxErp\Actions\SerialNumber\DeleteSerialNumber;
use FluxErp\Actions\SerialNumber\UpdateSerialNumber;
use FluxErp\Htmlables\TabButton;
use FluxErp\Http\Requests\CreateSerialNumberRequest;
use FluxErp\Http\Requests\UpdateSerialNumberRequest;
use FluxErp\Models\Address;
use FluxErp\Models\Product;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use WireUi\Traits\Actions;

class SerialNumber extends Component
{
    use Actions, WithTabs;

    public array $serialNumber = [
        'serial_number' => null,
        'product_id' => null,
    ];

    public ?string $productImage = '';

    public string $comment = '';

    public string $tab = 'product.serial-number.general';

    public bool $edit = false;

    protected array $queryString = [
        'tab' => ['except' => 'general'],
    ];

    public function getRules(): array
    {
        $rules = ($this->serialNumber['id'] ?? false) ?
            (new UpdateSerialNumberRequest())->rules() :
            (new CreateSerialNumberRequest())->rules();
        $additionalColumnRules = (new \FluxErp\Models\SerialNumber())->hasAdditionalColumnsValidationRules();

        return Arr::prependKeysWith(array_merge($rules, $additionalColumnRules), 'serialNumber.');
    }

    public function mount(int $id): void
    {
        if ($id > 0) {
            $serialNumber = \FluxErp\Models\SerialNumber::query()
                ->whereKey($id)
                ->with('product')
                ->firstOrFail();

            $this->comment = $serialNumber->comments()->latest()->first()?->comment ?? '';

            $this->serialNumber = $serialNumber->toArray();

            $this->productImage = $serialNumber->product?->getFirstMediaUrl('images');
        } else {
            $this->new();
        }

        if (request('addressId')) {
            $address = Address::query()
                ->whereKey(request('addressId'))
                ->firstOrFail();

            $this->serialNumber['address_id'] = $address->id;
            $this->serialNumber['address'] = $address->toArray();
        }
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.product.serial-number.serial-number');
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('product.serial-number.general')->label(__('General')),
            TabButton::make('product.serial-number.comments')->label(__('Comments')),
        ];
    }

    public function delete(): false|Redirector
    {
        $this->skipRender();

        try {
            DeleteSerialNumber::make($this->serialNumber)
                ->checkPermission()
                ->validate()
                ->execute();

            return redirect()->route('products.serial-numbers');
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        return false;
    }

    public function updatedSerialNumberProductId(int $id): void
    {
        $this->serialNumber['product'] = Product::query()
            ->whereKey($id)
            ->first()
            ?->toArray();
    }

    public function save(): void
    {
        $action = ($this->serialNumber['id'] ?? false) ? UpdateSerialNumber::class : CreateSerialNumber::class;

        try {
            $response = $action::make($this->serialNumber)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $response->load('product');
        $this->serialNumber = $response->toArray();

        $this->notification()->success(__('Serial number saved'));
        $this->edit = false;
    }

    public function startEdit(): void
    {
        $this->skipRender();
        $this->resetErrorBag();

        $this->edit = true;
    }

    public function cancel(): void
    {
        $this->skipRender();
        $this->resetErrorBag();

        if ($this->serialNumber['id'] ?? false) {
            $this->edit = false;
        } else {
            $this->redirect(route('products.serial-numbers'));
        }
    }

    public function new(): void
    {
        $this->reset();

        $this->edit = true;
    }
}

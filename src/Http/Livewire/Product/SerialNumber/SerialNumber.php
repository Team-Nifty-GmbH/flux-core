<?php

namespace FluxErp\Http\Livewire\Product\SerialNumber;

use FluxErp\Http\Requests\CreateSerialNumberRequest;
use FluxErp\Http\Requests\UpdateSerialNumberRequest;
use FluxErp\Models\Address;
use FluxErp\Models\Product;
use FluxErp\Services\SerialNumberService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class SerialNumber extends Component
{
    use Actions;

    public array $serialNumber = [
        'serial_number' => null,
        'product_id' => null,
    ];

    public ?string $productImage = '';

    public string $comment = '';

    public string $tab = 'general';

    public bool $edit = false;

    protected $queryString = [
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

    /**
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function delete()
    {
        if (! user_can('api.serial-numbers.{id}.delete')) {
            $this->notification()->error(__('You dont have the permission to do that.'));

            return;
        }

        (new SerialNumberService())->delete($this->serialNumber['id']);

        $this->notification()->success(__('Serial number deleted'));

        return redirect()->to(route('products.serial-numbers'));
    }

    public function updatedSerialNumberProductId($id): void
    {
        $this->serialNumber['product'] = Product::query()
            ->whereKey($id)
            ->first()
            ?->toArray();
    }

    public function save(): void
    {
        $function = ($this->serialNumber['id'] ?? false) ? 'update' : 'create';
        $permission = $function === 'update' ? 'api.serial-numbers.put' : 'api.serial-numbers.post';

        if (! user_can($permission)) {
            $this->notification()->error(__('You dont have the permission to do that.'));

            return;
        }

        $this->resetErrorBag();
        $validated = $this->validate()['serialNumber'];

        $service = new SerialNumberService();
        $response = $service->{$function}($validated);

        if ($response instanceof \FluxErp\Models\SerialNumber) {
            $response->load('product');
            $this->serialNumber = $response->toArray();
        } else {
            if (! ($response['data'] ?? false)) {
                foreach (Arr::prependKeysWith($response['errors'], 'serialNumber.') as $key => $error) {
                    $this->addError($key, $error);
                }

                return;
            }

            $response['data']->load('product');
            $this->serialNumber = $response['data']->toArray();
        }

        $this->notification()->success(__('Serial number saved'));
        $this->edit = false;
    }

    public function startEdit(): void
    {
        $this->skipRender();
        $this->resetErrorBag();

        $this->edit = true;
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function cancel()
    {
        $this->skipRender();
        $this->resetErrorBag();

        if ($this->serialNumber['id'] ?? false) {
            $this->edit = false;
        } else {
            return redirect()->to(route('products.serial-numbers'));
        }
    }

    public function new(): void
    {
        $this->reset();

        $this->edit = true;
    }
}

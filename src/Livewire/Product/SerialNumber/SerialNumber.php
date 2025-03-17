<?php

namespace FluxErp\Livewire\Product\SerialNumber;

use FluxErp\Actions\SerialNumber\UpdateSerialNumber;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\SerialNumberForm;
use FluxErp\Models\SerialNumber as SerialNumberModel;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class SerialNumber extends Component
{
    use Actions, WithTabs;

    public bool $edit = false;

    public ?string $productImage = '';

    public SerialNumberForm $serialNumber;

    public string $tab = 'product.serial-number.general';

    protected array $queryString = [
        'tab' => ['except' => 'general'],
    ];

    public function mount(int $id): void
    {
        $serialNumber = resolve_static(SerialNumberModel::class, 'query')
            ->whereKey($id)
            ->with([
                'addresses:id,name,address_serial_number.quantity',
                'product:products.id,products.name',
            ])
            ->firstOrFail();

        $this->serialNumber->fill($serialNumber);
        $this->productImage = $serialNumber->product?->getFirstMediaUrl('images');
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.product.serial-number.serial-number');
    }

    #[Renderless]
    public function cancel(): void
    {
        $this->skipRender();
        $this->serialNumber->reset();
        $this->mount($this->serialNumber->id);

        $this->edit = false;
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('product.serial-number.general')->text(__('General')),
            TabButton::make('product.serial-number.comments')->text(__('Comments')),
        ];
    }

    public function save(): void
    {
        try {
            $response = UpdateSerialNumber::make($this->serialNumber)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $response->load('product');
        $this->serialNumber = $response->toArray();

        $this->notification()->success(__(':model saved', ['model' => __('Serial Number')]))->send();
        $this->edit = false;
    }

    #[Renderless]
    public function startEdit(): void
    {
        $this->edit = true;
    }
}

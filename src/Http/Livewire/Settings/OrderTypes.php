<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Models\OrderType;
use FluxErp\Services\OrderTypeService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class OrderTypes extends Component
{
    use Actions;

    public array $orderTypes;

    public array $orderTypeSettings = [];

    public array $orderType = [];

    public string $orderTypeSlug = '';

    public bool $editModal = false;

    public $selectedOrderType = [
        'id' => null,
        'name' => '',
        'description' => '',
        'order_type_enum' => '',
        'is_active' => false,
        'is_hidden' => false,
    ];

    public function mount(): void
    {
        $this->orderTypes = get_subclasses_of(
            extendingClass: 'FluxErp\View\Printing\Order\OrderView',
            namespace: 'FluxErp\View\Printing\Order'
        );

        $this->orderTypeSettings = OrderType::query()
            ->get()
            ->keyBy('print_layouts')
            ->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.order-types');
    }

    public function save(): void
    {
        $orderTypesService = new OrderTypeService();
        $response = $orderTypesService->update($this->orderTypeSettings);

        if ($response['status'] !== 200) {
            $this->notification()->error(
                title: __('Order type setting could not be saved'),
                description: implode(', ', Arr::flatten($response['errors']))
            );
        }

        $this->detailModal = false;
        $this->skipRender();
    }

    public function show($orderTypeSlug): void
    {
        $this->orderTypeSlug = $orderTypeSlug;
        $this->detailModal = true;
        $this->orderType = data_get($this->orderTypeSettings, $orderTypeSlug);

        $orderType = OrderType::query()
            ->where('slug', $orderTypeSlug)
            ->first();

        if ($orderType) {
            $this->orderType['is_enabled'] = $orderType->is_enabled;
        } else {
            $this->orderType['is_enabled'] = false;
        }

        $this->skipRender();
    }

    public function showEditModal($orderTypeId = null)
    {
        if ($orderTypeId) {
            $orderType = OrderType::find($orderTypeId);

            if ($orderType) {
                $this->selectedOrderType = [
                    'id' => $orderType->id,
                    'name' => $orderType->name,
                    'description' => $orderType->description,
                    'order_type_enum' => $orderType->order_type_enum,
                    'is_active' => $orderType->is_active,
                    'is_hidden' => $orderType->is_hidden,
                ];
            }
        } else {
            $this->selectedOrderType = [
                'id' => null,
                'name' => '',
                'description' => '',
                'order_type_enum' => '',
                'is_active' => false,
                'is_hidden' => false,
            ];
        }

        $this->editModal = true;
    }
}

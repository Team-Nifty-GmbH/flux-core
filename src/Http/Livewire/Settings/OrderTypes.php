<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Models\Client;
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

    public array $selectedOrderType = [
        'id' => null,
        'name' => '',
        'client_id' => '',
        'description' => '',
        'order_type_enum' => '',
        'print_layouts' => [],
        'is_active' => false,
        'is_hidden' => false,
    ];

    public $clients;

    public function getRules(): array
    {
        return [
            'selectedOrderType.name' => 'required|string|max:255',
            'selectedOrderType.description' => 'nullable|string|max:500',
            'selectedOrderType.order_type_enum' => 'required|string|max:255',
            'selectedOrderType.print_layouts.*' => 'required|string|max:255',
            'selectedOrderType.is_active' => 'required|boolean',
            'selectedOrderType.is_hidden' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'selectedOrderType.name.required' => 'Order Type Name is required.',
            'selectedOrderType.description.max' => 'Description can have a maximum of 500 characters.',
            'selectedOrderType.order_type_enum.required' => 'Order Type Enum is required.',
            'selectedOrderType.print_layouts.*.required' =>
                'Print layout is required for each item in the print_layouts array.',
            'selectedOrderType.print_layouts.*.max' =>
                'Print layout can have a maximum of 255 characters for each item in the print_layouts array.',
            'selectedOrderType.is_active.required' => 'Is Active is required.',
            'selectedOrderType.is_hidden.required' => 'Is Hidden is required.',
        ];
    }

    public function mount(): void
    {
        $this->orderTypes = get_subclasses_of(
            extendingClass: 'FluxErp\View\Printing\Order\OrderView',
            namespace: 'FluxErp\View\Printing\Order'
        );

        $this->orderTypeSettings = OrderType::query()
            ->get()
            ->toArray();

        $this->clients = Client::query()
            ->get()
            ->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.order-types');
    }

    public function save(): void
    {
        $this->validate();

        if ($this->selectedOrderType['id']) {
            // Update the existing order type
            $orderType = OrderType::find($this->selectedOrderType['id']);
            $orderType->update($this->selectedOrderType);
        } else {
            // Create a new order type
            OrderType::create($this->selectedOrderType);
        }

        $this->editModal = false;
        $this->render();
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

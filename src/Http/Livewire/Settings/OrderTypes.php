<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Http\Requests\CreateOrderTypeRequest;
use FluxErp\Http\Requests\UpdateOrderTypeRequest;
use FluxErp\Models\Client;
use FluxErp\Models\OrderType;
use FluxErp\Services\OrderTypeService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class OrderTypes extends Component
{
    use Actions;

    public array $orderTypes;

    public bool $editModal = false;

    public array $selectedOrderType = [
        'id' => null,
        'name' => null,
        'client_id' => null,
        'description' => null,
        'order_type_enum' => null,
        'print_layouts' => [],
        'is_active' => false,
        'is_hidden' => false,
    ];

    public array $clients;

    public array $enum;

    public function getRules(): mixed
    {
        $orderTypeRequest = ($this->selectedOrderType['id'] ?? false)
            ? new UpdateOrderTypeRequest()
            : new CreateOrderTypeRequest();

        return Arr::prependKeysWith($orderTypeRequest->getRules($this->selectedOrderType),
            'selectedOrderType.');
    }

    public function messages(): array
    {
        $orderTypeMessages = [
            'name.required' => 'Order Type Name is required.',
            'description.max' => 'Description can have a maximum of 500 characters.',
            'order_type_enum.required' => 'Order Type Enum is required.',
            'print_layouts.*.required' => 'Print layout is required for each item in the print_layouts array.',
            'print_layouts.*.max' => 'Print layout can have a maximum of 255 characters for each item in the print_layouts array.',
            'is_active.required' => 'Is Active is required.',
            'is_hidden.required' => 'Is Hidden is required.',
        ];

        return Arr::prependKeysWith($orderTypeMessages, 'selectedOrderType.');
    }

    public function mount(): void
    {
        $this->orderTypes = get_subclasses_of(
            extendingClass: 'FluxErp\View\Printing\Order\OrderView',
            namespace: 'FluxErp\View\Printing\Order'
        );

        $this->clients = Client::query()
            ->get(['id', 'name'])
            ->toArray();

        $this->enum = OrderTypeEnum::values();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.order-types');
    }

    public function save(): void
    {
        $validatedData = $this->validate();

        if (isset($validatedData['id'])) {
            $orderType = OrderType::find($validatedData['id']);
            $orderType->update($validatedData);
        } else {
            OrderType::create($validatedData);
        }

        $this->editModal = false;
        $this->render();
    }

    public function showEditModal(?int $orderTypeId = null): void
    {
        $orderType = OrderType::query()
            ->whereKey($orderTypeId)
            ->first();

        if ($orderType) {
            $this->selectedOrderType = [
                'id' => $orderType->id,
                'name' => $orderType->name,
                'description' => $orderType->description,
                'order_type_enum' => $orderType->order_type_enum,
                'is_active' => $orderType->is_active,
                'is_hidden' => $orderType->is_hidden,
            ];
        } else {
            $this->selectedOrderType = [
                'id' => null,
                'name' => null,
                'description' => null,
                'order_type_enum' => null,
                'is_active' => false,
                'is_hidden' => false,
            ];
        }

        $this->editModal = true;
    }

    public function delete(): void
    {
        if (! user_can('api.order-types.{id}.delete')) {
            return;
        }
        (new OrderTypeService())->delete($this->selectedOrderType['id']);

        $this->skipRender();
    }
}

<?php

namespace FluxErp\Livewire\Settings;

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

    public array $clients;

    public array $enum;

    public array $orderType = [
        'id' => null,
        'name' => null,
        'client_id' => null,
        'description' => null,
        'print_layouts' => [],
        'order_type_enum' => null,
        'is_active' => false,
        'is_hidden' => false,
    ];

    public bool $editModal = false;

    public function getRules(): array
    {
        $orderTypeRequest = $this->orderType['id'] ?
            new UpdateOrderTypeRequest() :
            new CreateOrderTypeRequest();

        return Arr::prependKeysWith($orderTypeRequest->rules(), 'orderType.');
    }

    public function messages(): array
    {
        $orderTypeMessages = [
            'name.required' => 'Order Type Name is required.',
            'description.max' => 'Description can have a maximum of 500 characters.',
            'print_layouts.*.required' => 'Print layout is required for each item in the print_layouts array.',
            'print_layouts.*.max' => 'Print layout can have a maximum of 255 characters for each item in the print_layouts array.',
            'order_type_enum.required' => 'Order Type Enum is required.',
            'is_active.required' => 'Is Active is required.',
            'is_hidden.required' => 'Is Hidden is required.',
        ];

        return Arr::prependKeysWith($orderTypeMessages, 'orderType.');
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
        $validated = $this->validate()['orderType'];

        $orderType = OrderType::query()
            ->whereKey($validated['id'] ?? null)
            ->firstOrNew();

        $orderType->fill($validated);
        $orderType->save();

        $this->editModal = false;
        $this->skipRender();
        $this->dispatch('loadData')->to('data-tables.order-type-list');
    }

    public function showEditModal(?int $orderTypeId = null): void
    {
        $orderType = OrderType::query()
            ->whereKey($orderTypeId)
            ->first();

        if ($orderType) {
            $this->orderType = [
                'id' => $orderType->id,
                'name' => $orderType->name,
                'description' => $orderType->description,
                'print_layouts' => $orderType->print_layouts ?? [],
                'order_type_enum' => $orderType->order_type_enum,
                'is_active' => $orderType->is_active,
                'is_hidden' => $orderType->is_hidden,
            ];
        } else {
            $this->orderType = [
                'id' => null,
                'name' => null,
                'description' => null,
                'print_layouts' => [],
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
        (new OrderTypeService())->delete($this->orderType['id']);

        $this->dispatch('loadData')->to('data-tables.order-type-list');
        $this->skipRender();
    }
}

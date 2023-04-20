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

    public bool $detailModal = false;

    public function mount(): void
    {
        $this->orderTypes = config('order_types.available_order_types') ?? [];

        $orderTypeSettings = data_get($this->orderTypes, '*');

        foreach ($orderTypeSettings as $orderTypeSetting) {
            $this->orderTypeSettings[$orderTypeSetting] = [
                'is_enabled' => OrderType::query()
                    ->where('slug', $orderTypeSetting)
                    ->value('is_enabled')
            ];
        }
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
}

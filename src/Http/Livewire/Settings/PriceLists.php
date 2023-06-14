<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Http\Requests\CreateDiscountRequest;
use FluxErp\Http\Requests\CreatePriceListRequest;
use FluxErp\Http\Requests\UpdatePriceListRequest;
use FluxErp\Models\Country;
use FluxErp\Models\Discount;
use FluxErp\Models\PriceList;
use FluxErp\Services\DiscountService;
use FluxErp\Services\PriceListService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class PriceLists extends Component
{
    use Actions;

    public array $selectedPriceList = [
        'name' => '',
        'parent_id' => null,
        'price_list_code' => null,
        'is_net' => false,
        'is_default' => false
    ];

    public array $discount = [
        'discount' => null,
        'is_percentage' => false
    ];

    public array $priceLists = [];

    public bool $editModal = false;

    public function getRules(): array
    {
        $priceListRequest = ($this->selectedPriceList['id'] ?? false)
            ? new UpdatePriceListRequest()
            : new CreatePriceListRequest();

        $rules = Arr::prependKeysWith($priceListRequest->getRules($this->selectedPriceList), 'selectedPriceList.');

        if (!empty($this->discount['discount'])) {
            $discountRequest = new CreateDiscountRequest();
            $discountRules = Arr::prependKeysWith($discountRequest->getRules($this->discount), 'discount.');
            $rules = array_merge($rules, $discountRules);
        }

        return $rules;
    }

    public function mount(): void
    {
        $this->priceLists = PriceList::query()->get()->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.price-lists');
    }

    public function showEditModal(int|null $priceListId = null): void
    {
        $this->selectedPriceList = PriceList::query()->whereKey($priceListId)->first()?->toArray() ?: [
            'name' => '',
            'parent_id' => null,
            'price_list_code' => null,
            'is_net' => false,
            'is_default' => false
        ];

        $this->discount = [
            'discount' => null,
            'is_percentage' => false
        ];

        $this->editModal = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (! user_can('api.price-lists.post')) {
            return;
        }

        $validated = $this->validate();

        $priceList = PriceList::query()->whereKey($this->selectedPriceList['id'] ?? false)->firstOrNew();

        $function = $priceList->exists ? 'update' : 'create';

        $response = (new PriceListService())->{$function}($validated['selectedPriceList']);

        if (($response['status'] ?? false) === 200 || $response instanceof Model) {
            if (!empty($validated['discount']['discount'])) {

                $discount = new Discount($validated['discount']);

                $discount->model_type = PriceList::class;

                $discount->model_id = $response->id;

                $discountService = new DiscountService();

                $discountService->create($discount->toArray());
            }

            $this->notification()->success('Successfully saved');
            $this->editModal = false;
        }

        $this->mount();
    }

    public function delete(): void
    {
        if (! user_can('api.price-lists.{id}.delete')) {
            return;
        }

        $collection = collect($this->priceLists);
        Country::query()->whereKey($this->selectedPriceList['id'])->first()->delete();
        $this->priceLists = $collection->whereNotIn('id', [$this->selectedPriceList['id']])->toArray();
    }
}

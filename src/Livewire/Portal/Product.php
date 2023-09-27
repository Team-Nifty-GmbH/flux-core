<?php

namespace FluxErp\Livewire\Portal;

use FluxErp\Http\Requests\UpdateSerialNumberRequest;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\SerialNumber;
use FluxErp\Services\CommentService;
use FluxErp\Services\SerialNumberService;
use FluxErp\Traits\Livewire\WithAddressAuth;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use WireUi\Traits\Actions;

class Product extends Component
{
    use Actions, WithAddressAuth, WithFileUploads;

    public array $serialNumber;

    public array $product;

    public array $additionalColumns;

    public ?string $productImage;

    public string $comment = '';

    public function boot(): void
    {
        $this->additionalColumns = AdditionalColumn::query()
            ->where('model_type', SerialNumber::class)
            ->get()
            ->toArray();
    }

    /**
     * @return array|mixed
     */
    public function getRules(): mixed
    {
        return Arr::prependKeysWith(
            array_merge(
                (new UpdateSerialNumberRequest())->getRules($this->serialNumber),
                (new SerialNumber())->hasAdditionalColumnsValidationRules()
            ),
            'serialNumber.'
        );
    }

    public function mount($id): void
    {
        $addresses = Auth::user()
            ->contact
            ->addresses
            ->pluck('id')
            ->toArray();

        $serialNumber = SerialNumber::query()
            ->whereKey($id)
            ->whereIn('address_id', $addresses)
            ->with('product')
            ->firstOrFail();

        $this->comment = $serialNumber->comments()->latest()->first()?->comment ?? '';

        $this->serialNumber = $serialNumber->toArray();

        $this->productImage = $serialNumber->product?->getFirstMedia('images')?->toHtml();
    }

    public function render(): View
    {
        return view('flux::livewire.portal.product')
            ->layout('flux::components.layouts.portal');
    }

    public function save(): void
    {
        $validated = $this->validate();

        (new SerialNumberService())->update($validated['serialNumber']);

        (new CommentService())->create([
            'model_id' => $this->serialNumber['id'],
            'model_type' => SerialNumber::class,
            'comment' => $this->comment,
        ]);

        $this->notification()->success(__('Successfully saved'));
    }
}

<?php

namespace FluxErp\Livewire\Portal;

use FluxErp\Actions\SerialNumber\UpdateSerialNumber;
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
        $this->additionalColumns = app(AdditionalColumn::class)->query()
            ->where('model_type', app(SerialNumber::class)->getMorphClass())
            ->get()
            ->toArray();
    }

    public function getRules(): array
    {
        return Arr::prependKeysWith(
            UpdateSerialNumber::make([])->getRules(),
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

        $serialNumber = app(SerialNumber::class)->query()
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
        return view('flux::livewire.portal.product');
    }

    public function save(): void
    {
        $validated = $this->validate();

        app(SerialNumberService::class)->update($validated['serialNumber']);

        app(CommentService::class)->create([
            'model_id' => $this->serialNumber['id'],
            'model_type' => app(SerialNumber::class)->getMorphClass(),
            'comment' => $this->comment,
        ]);

        $this->notification()->success(__('Successfully saved'));
    }
}

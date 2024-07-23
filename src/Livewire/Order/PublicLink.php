<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\MediaForm;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\View\Layouts\Clear;
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

class PublicLink extends Component
{
    use Actions, WithFileUploads;

    public MediaForm $signature;

    #[Locked]
    public ?string $className;

    #[Locked]
    public ?int $orderId;

    #[Url(as: 'print-view')]
    public ?string $printView = null;

    public function mount(Order $order): void
    {
        $this->className = data_get($order->resolvePrintViews(), $this->printView) ?? abort(404);
        $this->orderId = $order->id;

        $media = app(Media::class)->query()
            ->where('model_id', $order->id)
            ->where('collection_name', 'signature')
            ->whereJsonContains('custom_properties->order_type', $this->printView)
            ->first();

        $this->signature->fill($media ?? []);
    }

    #[Renderless]
    public function save(): bool
    {
        // add to which type it belongs
        if (($this->signature->stagedFiles || $this->signature->id) && ! is_null($this->className)) {
            $this->signature->model_type = morph_alias(Order::class);
            $this->signature->model_id = $this->order->id;
            $this->signature->collection_name = 'signature';
            $this->signature->disk = 'local';
            $this->signature->custom_properties = ['order_type' => $this->printView];
            $this->signature->stagedFiles[0]['name'] = 'signature-' . $this->printView;
            $this->signature->stagedFiles[0]['file_name'] = data_get(
                $this->signature->stagedFiles[0],
                'temporary_filename',
                Uuid::uuid4()->toString()
            ) . '.png';
            try {
                $this->signature->save();

            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                return false;
            }
        }

        return true;
    }

    public function downloadSignatureAsUrlData(Media $media): string|bool
    {
        if (! file_exists($media->getPath())) {
            $this->notification()->error(__('File not found!'));

            return false;
        }

        $fileContent = file_get_contents($media->getPath());
        $base64File = base64_encode($fileContent);

        return 'data:image/png;base64,' . $base64File;

    }

    public function render(): View
    {
        // This ensures livewire recognizes the content and wraps the view around it
        // override the x-layouts.print with an empty div
        Clear::$includeAfter = 'flux::livewire.order.public-link';

        Blade::component('layouts.print', Clear::class);

        return resolve_static(Order::class, 'query')
            ->whereKey($this->orderId)
            ->firstOrFail()
            ->print()
            ->renderView($this->className)
            ->layout('flux::layouts.printing');
    }
}

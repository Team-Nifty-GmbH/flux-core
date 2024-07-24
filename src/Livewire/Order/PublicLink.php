<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\MediaForm;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
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
    public ?string $uuid;

    #[Url(as: 'print-view')]
    public ?string $printView = null;

    #[Url]
    public ?string $model = null;

    public function mount(): void
    {
        $model = $this->getModel();
        $this->className = data_get($model->resolvePrintViews(), $this->printView) ?? abort(404);

        $media = app(Media::class)->query()
            ->where('model_id', $model->id)
            ->where('collection_name', 'signature')
            ->whereJsonContains('custom_properties->order_type', $this->printView)
            ->first();

        $this->signature->fill($media ?? []);
    }

    public function save(): bool
    {
        // add to which type it belongs
        if (($this->signature->stagedFiles || $this->signature->id) && ! is_null($this->className)) {
            $this->signature->model_type = $this->model;
            $this->signature->model_id = $this->getModel()->getKey();
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

    #[Layout('flux::layouts.printing')]
    public function render(): string
    {
        // This ensures livewire recognizes the content and wraps the view around it
        // override the x-layouts.print with an empty div
        PrintableView::setLayout(null);

        return Blade::render(
            '<div>{!! $view !!} @include(\'flux::livewire.order.public-link\')</div>',
            [
                'view' => $this->getModel()
                    ->print()
                    ->renderView($this->className)
            ]
        );
    }

    protected function getModel()
    {
        return morphed_model($this->model)::query()
            ->where('uuid', $this->uuid)
            ->firstOrFail();
    }
}

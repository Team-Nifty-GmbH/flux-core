<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Livewire\Forms\MediaForm;
use FluxErp\Models\Media;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

class SignaturePublicLink extends Component
{
    use Actions, WithFileUploads;

    public MediaForm $signature;

    #[Locked]
    public ?string $uuid;

    #[Url(as: 'print-view')]
    public ?string $printView = null;

    #[Url]
    public ?string $model = null;

    public $signatureUpload = null;

    public function mount(): void
    {
        if (! $this->uuid || ! $this->printView || ! $this->model) {
            abort(404);
        }

        $this->signature->fill(
            resolve_static(Media::class, 'query')
                ->where('model_id', $this->getModel()->id)
                ->where('model_type', $this->model)
                ->where('collection_name', 'signature')
                ->where('name', 'signature-'.$this->printView)
                ->firstOr(fn () => [])
        );
        $this->signature->custom_properties['name'] ??= auth()->user()?->name;
    }

    public function save(): bool
    {
        // add to which type it belongs
        if ($this->signature->stagedFiles || $this->signature->id) {
            Validator::make(
                ['name' => data_get($this->signature->custom_properties, 'name')],
                ['name' => 'required|string|max:255']
            )->validate();

            $this->signature->fill([
                'name' => 'signature-'.$this->printView,
                'model_type' => $this->model,
                'model_id' => $this->getModel()->getKey(),
                'collection_name' => 'signature',
                'disk' => 'local',
                'stagedFiles' => [
                    array_merge(
                        $this->signature->stagedFiles[0],
                        [
                            'name' => 'signature-'.$this->printView,
                            'file_name' => data_get(
                                $this->signature->stagedFiles[0],
                                'temporary_filename',
                                Uuid::uuid4()->toString()
                            ).'.png',
                        ]
                    ),
                ],
            ]);

            try {
                $this->signature->save();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
                $this->skipRender();

                return false;
            }
        }

        activity()
            ->performedOn($this->getModel())
            ->event('signature_added')
            ->log(__(
                ':view signature has been added by :name',
                [
                    'view' => $this->printView,
                    'name' => auth()->user()?->name
                        ?? data_get($this->signature->custom_properties, 'name'),
                ]
            ));

        return true;
    }

    #[Layout('flux::layouts.printing')]
    public function render(): string
    {
        // This ensures livewire recognizes the content and wraps the view around it
        // override the x-layouts.print with an empty div
        PrintableView::setLayout(null);

        return Blade::render(
            '<div>{!! $view !!} @include(\'flux::livewire.features.signature-public-link\')</div>',
            [
                'view' => $this->getModel()->print()->renderView($this->getPrintClass()),
            ]
        );
    }

    protected function getModel(): Model
    {
        return Cache::store('array')->rememberForever(
            'flux-erp.signature-public-link.'.$this->uuid,
            fn () => morphed_model($this->model)::query()
                ->where('uuid', $this->uuid)
                ->firstOrFail()
        );
    }

    protected function getPrintClass(): string
    {
        return data_get($this->getModel()->resolvePrintViews(), $this->printView) ?? abort(404);
    }
}

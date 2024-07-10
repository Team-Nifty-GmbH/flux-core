<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\MediaForm;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

class PublicLink extends Component
{
    use Actions, WithFileUploads;

    public Order $order;

    public MediaForm $signature;

    public ?string $className;

    #[Url]
    public ?string $orderType = null;

    public function mount(): void
    {
        $queryClass = implode('', array_map('ucfirst', explode('-', $this->orderType)));

        if (class_exists('FluxErp\\View\\Printing\\Order\\' . $queryClass)) {
            $this->className = 'FluxErp\\View\\Printing\\Order\\' . $queryClass;

            $media = app(Media::class)->query()
                ->where('model_id', $this->order->id)
                ->where('collection_name', 'signature')
                ->whereJsonContains('custom_properties->order_type', $this->orderType)
                ->first();

            $this->signature->fill($media ?? []);
        } else {
            abort(404);
        }
    }

    public function save(): bool
    {
        // add to which type it belongs
        if (($this->signature->stagedFiles || $this->signature->id) && ! is_null($this->className)) {
            $this->signature->model_type = Relation::getMorphClassAlias(Order::class);
            $this->signature->model_id = $this->order->id;
            $this->signature->collection_name = 'signature';
            $this->signature->disk = 'local';
            $this->signature->custom_properties = ['order_type' => $this->orderType];
            $this->signature->stagedFiles[0]['name'] = 'signature-' . $this->orderType;
            $this->signature->stagedFiles[0]['file_name'] = data_get(
                $this->signature->stagedFiles[0],
                'temporary_filename',
                Uuid::uuid4()->toString() . '.png'
            );
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

        return "data:image/png;base64,$base64File";

    }

    public function render()
    {
        return view('flux::livewire.order.public-link')->layout('flux::layouts.empty');
    }
}

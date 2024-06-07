<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\MediaForm;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

class PublicLink extends Component
{
    use Actions, WithFileUploads;

    public Order $order;

    public MediaForm $signature;

    public function mount():void
    {
         $media = app(Media::class)::where('model_id', $this->order->id)->where('collection_name','signature')->first();
         if(!is_null($media)){
            $this->signature->fill($media);
         }
    }

    public function save(): string | null
    {
        $this->signature->model_type = app(Order::class)->getMorphClass();
        $this->signature->model_id = $this->order->id;
        $this->signature->collection_name = 'signature';
        $this->signature->stagedFiles[0]['name'] = 'signature';
        $this->signature->stagedFiles[0]['file_name'] = $this->signature->stagedFiles[0]['temporary_filename'] ?? Uuid::uuid4()->toString() . '.png';

        if ($this->signature->stagedFiles || $this->signature->id) {
            try {
                $this->signature->save();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                return null;
            }
        }

        $this->notification()->success(__('Signature saved'));
        return $this->signature->stagedFiles[0]['preview_url'] ?? null;
    }

    public function render()
    {
        return view('flux::livewire.order.public-link')->layout('flux::layouts.empty');
    }
}

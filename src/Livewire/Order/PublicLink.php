<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\MediaForm;
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

    public function save(): void
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

                return;
            }
        }

        $this->notification()->success(__('Signature saved'));
    }

    public function render()
    {
        return view('flux::livewire.order.public-link');
    }
}

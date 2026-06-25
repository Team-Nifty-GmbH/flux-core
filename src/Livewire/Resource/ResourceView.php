<?php

namespace FluxErp\Livewire\Resource;

use Exception;
use FluxErp\Livewire\Forms\ResourceBookingForm;
use FluxErp\Livewire\Forms\ResourceForm;
use FluxErp\Models\Resource;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ResourceView extends Component
{
    use Actions;

    public bool $edit = false;

    public ?string $avatarUrl = '';

    public ResourceBookingForm $resourceBookingForm;

    public ResourceForm $resourceForm;

    public function mount(int $id): void
    {
        $resource = Resource::query()
            ->whereKey($id)
            ->with(['product:id,name'])
            ->firstOrFail();

        $this->resourceForm->fill($resource);
        $this->avatarUrl = $resource->getFirstMediaUrl('avatar');
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.resource.resource-view');
    }

    public function save(): bool
    {
        try {
            $this->resourceForm->save();
        } catch (Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->toast()
            ->success(__(':model saved', ['model' => __('Resource')]))
            ->send();
        $this->edit = false;

        return true;
    }

    public function cancel(): void
    {
        $this->resourceForm->reset();
        $this->mount($this->resourceForm->id);

        $this->edit = false;
    }

    #[Renderless]
    public function startEdit(): void
    {
        $this->edit = true;
    }

    public function saveBooking(): bool
    {
        try {
            $this->resourceBookingForm->save();
        } catch (Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->toast()
            ->success(__(':model saved', ['model' => __('Resource Booking')]))
            ->send();
        $this->resourceBookingForm->reset();

        return true;
    }

    public function editBooking(int $id): void
    {
        $this->resourceBookingForm->fill(
            \FluxErp\Models\ResourceBooking::query()->whereKey($id)->firstOrFail()
        );

        $this->modalOpen($this->resourceBookingForm->modalName());
    }

    public function newBooking(): void
    {
        $this->resourceBookingForm->reset();
        $this->resourceBookingForm->resource_id = $this->resourceForm->id;

        $this->modalOpen($this->resourceBookingForm->modalName());
    }
}

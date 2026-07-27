<?php

namespace FluxErp\Livewire\Resource;

use Exception;
use FluxErp\Livewire\DataTables\ResourceBookingList;
use FluxErp\Livewire\Forms\ResourceBookingForm;
use FluxErp\Livewire\Forms\ResourceForm;
use FluxErp\Models\Resource;
use FluxErp\Models\ResourceBooking;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class ResourceView extends Component
{
    use Actions;

    public ?string $avatarUrl = '';

    public bool $edit = false;

    public ResourceBookingForm $resourceBookingForm;

    public ResourceForm $resourceForm;

    public function mount(int $id): void
    {
        $resource = resolve_static(Resource::class, 'query')
            ->whereKey($id)
            ->firstOrFail();

        $this->resourceForm->fill($resource);
        $this->avatarUrl = $resource->getFirstMediaUrl('avatar');
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.resource.resource-view');
    }

    public function cancel(): void
    {
        $id = $this->resourceForm->id;
        $this->resourceForm->reset();
        $this->mount($id);
        $this->edit = false;
    }

    public function editBooking(int $id): void
    {
        $booking = resolve_static(ResourceBooking::class, 'query')
            ->whereKey($id)
            ->firstOrFail();

        $this->resourceBookingForm->fill($booking);
        $this->resourceBookingForm->start = $booking->start?->format('Y-m-d\TH:i');
        $this->resourceBookingForm->end = $booking->end?->format('Y-m-d\TH:i');

        $this->modalOpen($this->resourceBookingForm->modalName());
    }

    public function newBooking(): void
    {
        $this->resourceBookingForm->reset();
        $this->resourceBookingForm->resource_id = $this->resourceForm->id;

        $this->modalOpen($this->resourceBookingForm->modalName());
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
        $this->dispatch('dataTableReload')->to(ResourceBookingList::class);

        return true;
    }

    #[Renderless]
    public function startEdit(): void
    {
        $this->edit = true;
    }
}

<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Http\Requests\CreateTicketTypeRequest;
use FluxErp\Http\Requests\UpdateTicketTypeRequest;
use FluxErp\Services\TicketTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class TicketTypeEdit extends Component
{
    use Actions;

    public array $ticketType;

    public array $models;

    public bool $isNew = true;

    protected $listeners = [
        'show',
        'save',
        'delete',
    ];

    public function getRules(): array
    {
        $rules = $this->isNew ?
            (new CreateTicketTypeRequest())->rules() : (new UpdateTicketTypeRequest())->getRules($this->ticketType);

        return Arr::prependKeysWith($rules, 'ticketType.');
    }

    public function boot(): void
    {
        $this->ticketType = array_fill_keys(
            array_keys((new CreateTicketTypeRequest())->rules()),
            null
        );

        $appModels = get_subclasses_of(Model::class, 'FluxErp\\');
        $moduleModels = get_subclasses_of(Model::class, 'Modules\\');

        $this->models = array_merge($appModels, $moduleModels);
    }

    public function render(): View
    {
        return view('flux::livewire.settings.ticket-type-edit');
    }

    public function show(array $ticketType = []): void
    {
        $this->ticketType = $ticketType ?:
            array_fill_keys(
                array_keys((new CreateTicketTypeRequest())->rules()),
                null
            );

        $this->isNew = ! array_key_exists('id', $this->ticketType);
    }

    public function save(): void
    {
        if (($this->isNew && ! user_can('api.ticket-types.{id}.post')) ||
            (! $this->isNew && ! user_can('api.ticket-types.{id}.put'))
        ) {
            $this->notification()->error(
                __('insufficient permissions'),
                __('You have not the rights to modify this record')
            );

            return;
        }

        $validated = $this->validate();

        $ticketTypeService = new TicketTypeService();
        $response = $ticketTypeService->{$this->isNew ? 'create' : 'update'}($validated['ticketType']);

        if (! $this->isNew && $response['status'] > 299) {
            $this->notification()->error(
                implode(',', array_keys($response['errors'])),
                implode(', ', Arr::dot($response['errors']))
            );

            return;
        }

        $this->notification()->success(__('Ticket type saved successful.'));

        $ticketType = $this->isNew ? $response->toArray() : $response['data']->toArray();

        $this->skipRender();
        $this->dispatch('closeModal', $ticketType);
    }

    public function delete(): void
    {
        if (! user_can('api.ticket-types.{id}.delete')) {
            return;
        }

        (new TicketTypeService())->delete($this->ticketType['id']);

        $this->skipRender();
        $this->dispatch('closeModal', $this->ticketType, true);
    }
}

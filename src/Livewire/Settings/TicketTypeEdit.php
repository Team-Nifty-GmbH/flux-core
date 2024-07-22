<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\TicketType\CreateTicketType;
use FluxErp\Actions\TicketType\DeleteTicketType;
use FluxErp\Actions\TicketType\UpdateTicketType;
use FluxErp\Models\Role;
use FluxErp\Models\TicketType;
use FluxErp\Rulesets\TicketType\CreateTicketTypeRuleset;
use FluxErp\Services\TicketTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;
use WireUi\Traits\Actions;

class TicketTypeEdit extends Component
{
    use Actions;

    public array $ticketType;

    public array $models;

    public array $roles;

    public bool $isNew = true;

    protected $listeners = [
        'show',
        'save',
        'delete',
    ];

    public function getRules(): array
    {
        $rules = ($this->isNew ? CreateTicketType::make([]) : UpdateTicketType::make($this->ticketType))->getRules();

        return Arr::prependKeysWith($rules, 'ticketType.');
    }

    public function mount(): void
    {
        $this->ticketType = array_fill_keys(
            array_keys(resolve_static(CreateTicketTypeRuleset::class, 'getRules')),
            null
        );

        $this->models = model_info_all()
            ->unique('morphClass')
            ->map(fn ($modelInfo) => [
                'label' => __(Str::headline($modelInfo->morphClass)),
                'value' => $modelInfo->morphClass,
            ])
            ->sortBy('label')
            ->toArray();

        $this->roles = resolve_static(Role::class, 'query')
            ->where('guard_name', 'web')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.ticket-type-edit');
    }

    public function show(array $ticketType = []): void
    {
        $this->ticketType = $ticketType ?:
            array_fill_keys(
                array_keys(resolve_static(CreateTicketTypeRuleset::class, 'getRules')),
                null
            );

        unset($this->ticketType['uuid']);

        $this->isNew = ! array_key_exists('id', $this->ticketType);

        $this->ticketType['roles'] = $this->isNew ? [] : resolve_static(TicketType::class, 'query')
            ->join('role_ticket_type AS rtt', 'ticket_types.id', '=', 'rtt.ticket_type_id')
            ->whereKey($this->ticketType['id'])
            ->pluck('rtt.role_id')
            ->toArray();
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
        $this->dispatch('closeModal', $ticketType)->to('settings.ticket-types');
    }

    public function delete(): void
    {
        if (! resolve_static(DeleteTicketType::class, 'canPerformAction', [false])) {
            return;
        }

        (new TicketTypeService())->delete($this->ticketType['id']);

        $this->skipRender();
        $this->dispatch('closeModal', $this->ticketType, true)->to('settings.ticket-types');
    }
}

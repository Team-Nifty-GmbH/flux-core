<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\TicketType\CreateTicketType;
use FluxErp\Actions\TicketType\UpdateTicketType;
use FluxErp\Livewire\Forms\TicketTypeForm;
use FluxErp\Models\Role;
use FluxErp\Models\TicketType;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class TicketTypeEdit extends Component
{
    use Actions;

    public bool $isNew = true;

    public array $models;

    public array $roles;

    public TicketTypeForm $ticketType;

    protected $listeners = [
        'show',
        'save',
        'delete',
    ];

    public function mount(): void
    {
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

    #[Renderless]
    public function delete(): void
    {
        $ticketType = ['id' => $this->ticketType->id];
        try {
            $this->ticketType->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->dispatch('closeModal', $ticketType, true)->to('settings.ticket-types');
    }

    public function getRules(): array
    {
        $rules = ($this->isNew ? CreateTicketType::make([]) : UpdateTicketType::make($this->ticketType))->getRules();

        return Arr::prependKeysWith($rules, 'ticketType.');
    }

    #[Renderless]
    public function save(): void
    {
        try {
            $this->ticketType->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->notification()->success(__(':model saved', ['model' => __('Ticket Type')]))->send();
        $this->dispatch('closeModal', $this->ticketType)->to('settings.ticket-types');
    }

    public function show(array $ticketType = []): void
    {
        $this->ticketType->reset();
        $this->ticketType->fill($ticketType);

        $this->isNew = ! $this->ticketType->id;

        $this->ticketType->roles = $this->isNew
            ? []
            : resolve_static(TicketType::class, 'query')
                ->join('role_ticket_type AS rtt', 'ticket_types.id', '=', 'rtt.ticket_type_id')
                ->whereKey($this->ticketType->id)
                ->pluck('rtt.role_id')
                ->toArray();
    }
}

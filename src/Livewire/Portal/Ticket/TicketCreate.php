<?php

namespace FluxErp\Livewire\Portal\Ticket;

use Exception;
use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Rulesets\Ticket\CreateTicketRuleset;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TicketCreate extends Component
{
    use Actions, WithFileUploads;

    public array $additionalColumns;

    public $attachments = [];

    public string $modelType = Ticket::class;

    #[Locked]
    public ?int $oldTicketTypeId = null;

    public array $selectedAdditionalColumns = [];

    public array $ticket;

    public ?int $ticketTypeId = null;

    public array $ticketTypes;

    protected $listeners = [
        'show',
        'save',
    ];

    public function mount(?string $modelType = null, ?int $modelId = null): void
    {
        try {
            $modelType = $modelType ? app($modelType)->getMorphClass() : null;
        } catch (Exception) {
            $modelType = null;
        }

        $this->ticket = [
            'title' => null,
            'description' => null,
            'model_type' => $modelType,
            'model_id' => $modelId,
        ];

        $this->ticketTypes = resolve_static(TicketType::class, 'query')
            ->with('additionalModelColumns:id,name,model_type,model_id,field_type,values')
            ->when(
                $modelType,
                fn (Builder $query) => $query->where(
                    function (Builder $query) use ($modelType): void {
                        $query->where('model_type', $modelType)
                            ->orWhereNull('model_type');
                    }),
                fn (Builder $query) => $query->whereNull('model_type')
            )
            ->get()
            ->toArray();

        $this->additionalColumns = resolve_static(AdditionalColumn::class, 'query')
            ->where('model_type', app(Ticket::class)->getMorphClass())
            ->whereNull('model_id')
            ->select(['id', 'name', 'model_type', 'model_id', 'field_type', 'values'])
            ->get()
            ->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.portal.ticket.ticket-create');
    }

    public function getRules(): array
    {
        return Arr::prependKeysWith(resolve_static(CreateTicketRuleset::class, 'getRules'), 'ticket.');
    }

    public function save(): bool
    {
        $this->ticket = array_merge($this->ticket, [
            'authenticatable_type' => Auth::user()->getMorphClass(),
            'authenticatable_id' => Auth::id(),
            'ticket_type_id' => $this->ticketTypeId,
        ]);

        try {
            $ticket = CreateTicket::make($this->ticket)
                ->validate()
                ->execute();
        } catch (Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        try {
            $this->saveFileUploadsToMediaLibrary('attachments', $ticket->id, app(Ticket::class)->getMorphClass());
        } catch (Exception $e) {
            exception_to_notifications($e, $this);
        }

        $this->notification()->success(__('Ticket created…'))->send();

        $this->skipRender();
        $this->dispatch('closeModal', $ticket->toArray());
        $this->dispatch('loadData')->to('portal.data-tables.ticket-list');

        return true;
    }

    public function show(): void
    {
        $this->ticket = [
            'title' => null,
            'description' => null,
        ];

        $this->ticketTypeId = null;

        $this->attachments = [];
        $this->filesArray = [];

        $this->skipRender();
    }

    public function updatedAttachments(): void
    {
        $this->prepareForMediaLibrary('attachments');

        $this->skipRender();
    }

    public function updatedTicketTypeId(): void
    {
        $ticketTypeAdditionalColumns = array_filter(array_map(
            fn ($item) => data_get($item, 'additional_model_columns'),
            Arr::keyBy($this->ticketTypes, 'id')
        ));

        $oldAdditionalColumns = array_column(
            data_get($ticketTypeAdditionalColumns, $this->oldTicketTypeId, []),
            'name'
        );

        $this->oldTicketTypeId = $this->ticketTypeId;

        $this->selectedAdditionalColumns = $this->ticketTypeId ?
            $ticketTypeAdditionalColumns[$this->ticketTypeId] ?? [] : [];

        $this->ticket = Arr::except(
            array_merge(
                array_fill_keys(array_column($this->selectedAdditionalColumns, 'name'), null),
                $this->ticket,
            ),
            $oldAdditionalColumns
        );

        $this->skipRender();
    }
}

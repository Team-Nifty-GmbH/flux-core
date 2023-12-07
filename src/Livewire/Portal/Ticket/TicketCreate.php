<?php

namespace FluxErp\Livewire\Portal\Ticket;

use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Http\Requests\CreateTicketRequest;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use WireUi\Traits\Actions;

class TicketCreate extends Component
{
    use Actions, WithFileUploads;

    public array $ticket;

    public array $ticketTypes;

    public array $additionalColumns;

    public array $selectedAdditionalColumns = [];

    public ?int $ticketTypeId = null;

    public $attachments = [];

    public string $modelType = Ticket::class;

    private ?int $oldTicketTypeId = null;

    protected $listeners = [
        'show',
        'save',
    ];

    public function mount(?string $modelType = null, ?int $modelId = null): void
    {
        $this->ticket = [
            'title' => null,
            'description' => null,
            'model_type' => $modelType,
            'model_id' => $modelId,
        ];

        $this->ticketTypes = TicketType::query()
            ->with('additionalModelColumns:id,name,model_type,model_id,field_type,values')
            ->when(
                $modelType,
                fn (Builder $query) => $query->where(
                    function (Builder $query) use ($modelType) {
                        $query->where('model_type', $modelType)
                            ->orWhereNull('model_type');
                    }),
                fn (Builder $query) => $query->whereNull('model_type')
            )
            ->get()
            ->toArray();

        $this->additionalColumns = AdditionalColumn::query()
            ->where('model_type', Ticket::class)
            ->whereNull('model_id')
            ->select(['id', 'name', 'model_type', 'model_id', 'field_type', 'values'])
            ->get()
            ->toArray();
    }

    public function getRules(): array
    {
        return Arr::prependKeysWith((new CreateTicketRequest())->rules(), 'ticket.');
    }

    public function render(): View
    {
        return view('flux::livewire.portal.ticket.ticket-create');
    }

    public function updatedAttachments(): void
    {
        $this->prepareForMediaLibrary('attachments');

        $this->skipRender();
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
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        try {
            $this->saveFileUploadsToMediaLibrary('attachments', $ticket->id, Ticket::class);
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        $this->notification()->success(__('Ticket created…'));

        $this->skipRender();
        $this->dispatch('closeModal', $ticket->toArray());
        $this->dispatch('loadData')->to('portal.data-tables.ticket-list');

        return true;
    }

    public function updatedTicketTypeId(): void
    {
        $ticketTypeAdditionalColumns = array_filter(array_map(
            fn ($item) => data_get($item, 'additional_model_columns'),
            Arr::keyBy($this->ticketTypes, 'id')
        ));

        $oldAdditionalColumns = array_column(
            $ticketTypeAdditionalColumns[$this->oldTicketTypeId] ?? [],
            'name'
        );

        $this->ticket = array_merge($this->ticket, array_fill_keys($oldAdditionalColumns, null));
        $this->oldTicketTypeId = $this->ticketTypeId;

        $this->selectedAdditionalColumns = $this->ticketTypeId ?
            $ticketTypeAdditionalColumns[$this->ticketTypeId] ?? [] : [];

        $this->skipRender();
    }
}

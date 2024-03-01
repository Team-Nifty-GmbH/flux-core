<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class TicketList extends DataTable
{
    use HasEloquentListeners, WithFileUploads;

    protected string $view = 'flux::livewire.ticket.tickets';

    public array $enabledCols = [
        'ticket_number',
        'ticket_type.name',
        'user',
        'related',
        'title',
        'state',
        'created_at',
    ];

    protected string $model = Ticket::class;

    public array $ticket;

    #[Locked]
    public ?string $modelType = null;

    #[Locked]
    public ?int $modelId = null;

    public ?int $ticketTypeId = null;

    public array $selectedAdditionalColumns = [];

    public array $ticketTypes;

    public array $additionalColumns;

    public $attachments;

    private ?int $oldTicketTypeId = null;

    public bool $showTicketModal = false;

    public function mount(): void
    {
        $this->ticket = [
            'title' => null,
            'description' => null,
            'ticket_type_id' => null,
        ];

        $modelType = $this->modelType ? app($this->modelType)->getMorphClass() : null;

        $this->ticketTypes = app(TicketType::class)->query()
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

        $this->additionalColumns = app(AdditionalColumn::class)->query()
            ->where('model_type', app(Ticket::class)->getMorphClass())
            ->whereNull('model_id')
            ->select(['id', 'name', 'model_type', 'model_id', 'field_type', 'values'])
            ->get()
            ->toArray();

        parent::mount();
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.show()',
                ]),
        ];
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with([
            'ticketType:id,name',
            'authenticatable',
            'model',
        ]);
    }

    public function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);

        /** @var Ticket $item */
        if ($related = $item->morphTo('model')->getResults()) {
            $returnArray['related'] = method_exists($related, 'getLabel') ? $related->getLabel() : null;
        }

        $returnArray['user'] = $item->authenticatable?->getLabel();

        return $returnArray;
    }

    public function show(): void
    {
        $this->ticket = [
            'title' => null,
            'description' => null,
        ];

        $this->ticketTypeId = null;
        $this->selectedAdditionalColumns = [];
        $this->filesArray = [];
        $this->attachments = [];

        $this->showTicketModal = true;
    }

    public function save(): void
    {
        $this->ticket = array_merge($this->ticket, [
            'authenticatable_type' => Auth::user()->getMorphClass(),
            'authenticatable_id' => Auth::id(),
            'ticket_type_id' => $this->ticketTypeId,
        ]);

        if ($this->modelType && $this->modelId) {
            $this->ticket['model_type'] = app($this->modelType)->getMorphClass();
            $this->ticket['model_id'] = $this->modelId;
        }

        try {
            $ticket = CreateTicket::make($this->ticket)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        try {
            $this->saveFileUploadsToMediaLibrary('attachments', $ticket->id, app(Ticket::class)->getMorphClass());
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        $this->notification()->success(__('Ticket created…'));

        $this->showTicketModal = false;
        $this->skipRender();
        $this->loadData();
    }

    public function updatedAttachments(): void
    {
        $this->prepareForMediaLibrary('attachments');
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
    }
}

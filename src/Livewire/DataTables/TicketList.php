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
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class TicketList extends BaseDataTable
{
    use WithFileUploads;

    public array $additionalColumns;

    public $attachments;

    public array $enabledCols = [
        'ticket_number',
        'ticket_type.name',
        'user',
        'related',
        'title',
        'state',
        'created_at',
    ];

    #[Locked]
    public ?int $modelId = null;

    #[Locked]
    public ?string $modelType = null;

    public array $selectedAdditionalColumns = [];

    public bool $showTicketModal = false;

    public array $ticket;

    public ?int $ticketTypeId = null;

    public array $ticketTypes;

    protected ?string $includeBefore = 'flux::livewire.ticket.tickets';

    protected string $model = Ticket::class;

    private ?int $oldTicketTypeId = null;

    public function mount(): void
    {
        $this->ticket = [
            'title' => null,
            'description' => null,
            'ticket_type_id' => null,
        ];

        $modelType = $this->modelType ? morph_alias($this->modelType) : null;

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
            ->where('model_type', morph_alias(Ticket::class))
            ->whereNull('model_id')
            ->select(['id', 'name', 'model_type', 'model_id', 'field_type', 'values'])
            ->get()
            ->toArray();

        parent::mount();
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => '$wire.show()',
                ]),
        ];
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
            $this->saveFileUploadsToMediaLibrary('attachments', $ticket->id, morph_alias(Ticket::class));
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        $this->notification()->success(__('Ticket created…'))->send();

        $this->showTicketModal = false;
        $this->skipRender();
        $this->loadData();
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

    public function updatedAttachments(): void
    {
        $this->prepareForMediaLibrary('attachments');
    }

    public function updatedTicketTypeId(): void
    {
        $ticketTypeAdditionalColumns = array_filter(array_map(
            fn (array $item) => data_get($item, 'additional_model_columns'),
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

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with([
            'ticketType:id,name',
            'authenticatable',
            'model',
        ]);
    }

    protected function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);

        /** @var Ticket $item */
        if ($related = $item->morphTo('model')->getResults()) {
            $returnArray['related'] = method_exists($related, 'getLabel') ? $related->getLabel() : null;
        }

        $returnArray['user'] = $item->authenticatable?->getLabel();

        return $returnArray;
    }
}

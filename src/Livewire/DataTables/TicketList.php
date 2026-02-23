<?php

namespace FluxErp\Livewire\DataTables;

use Exception;
use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Actions\WorkTime\CreateWorkTime;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class TicketList extends BaseDataTable
{
    use WithFileUploads;

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

    public bool $showTicketModal = false;

    public array $ticket;

    public array $ticketTypes;

    protected ?string $includeBefore = 'flux::livewire.ticket.tickets';

    protected string $model = Ticket::class;

    public function mount(): void
    {
        $this->ticket = [
            'ticket_type_id' => null,
            'title' => null,
            'description' => null,
        ];

        $this->ticketTypes = resolve_static(TicketType::class, 'query')
            ->get(['id', 'name'])
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

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('clock')
                ->text(__('Track Time'))
                ->when(fn () => resolve_static(CreateWorkTime::class, 'canPerformAction', [false]))
                ->wireClick('startTimeTracking(record.id)'),
        ];
    }

    #[Renderless]
    public function save(): void
    {
        $this->ticket = array_merge($this->ticket, [
            'authenticatable_type' => Auth::user()->getMorphClass(),
            'authenticatable_id' => Auth::id(),
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
        } catch (Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        try {
            $this->saveFileUploadsToMediaLibrary('attachments', $ticket->id, morph_alias(Ticket::class));
        } catch (Exception $e) {
            exception_to_notifications($e, $this);
        }

        $this->toast()
            ->success(__('Ticket created…'))
            ->send();

        $this->showTicketModal = false;
        $this->loadData();
    }

    public function show(): void
    {
        $this->ticket = [
            'ticket_type_id' => null,
            'title' => null,
            'description' => null,
        ];

        $this->filesArray = [];
        $this->attachments = [];

        $this->showTicketModal = true;
    }

    #[Renderless]
    public function startTimeTracking(Ticket $ticket): void
    {
        $ticket->title = json_encode($ticket->title);
        $ticket->description = json_encode($ticket->description);

        $this->js(<<<JS
            \$dispatch(
                'start-time-tracking',
                {
                    trackable_type: '{$ticket->getMorphClass()}',
                    trackable_id: {$ticket->getKey()},
                    name: {$ticket->title},
                    description: {$ticket->description}
                }
            );
        JS);
    }

    public function updatedAttachments(): void
    {
        $this->prepareForMediaLibrary('attachments');
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

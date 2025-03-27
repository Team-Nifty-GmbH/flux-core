<?php

namespace FluxErp\Livewire\Portal\Ticket;

use FluxErp\Actions\Ticket\UpdateTicket;
use FluxErp\Htmlables\TabButton;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Ticket as TicketModel;
use FluxErp\Models\TicketType;
use FluxErp\States\Ticket\Closed;
use FluxErp\States\Ticket\Escalated;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Ticket extends Component
{
    use WithTabs;

    public array $additionalColumns = [];

    public array $attachments = [];

    public string $tab = 'portal.ticket.comments';

    public array $ticket;

    public function mount(int $id): void
    {
        $ticket = resolve_static(TicketModel::class, 'query')
            ->whereKey($id)
            ->with('users:id,name')
            ->firstOrFail();

        $this->additionalColumns = resolve_static(AdditionalColumn::class, 'query')
            ->where(function (Builder $query) use ($ticket): void {
                $query->where('model_type', app(TicketModel::class)->getMorphClass())
                    ->when($ticket->ticket_type_id, function (Builder $query) use ($ticket): void {
                        $query->orWhere(function (Builder $query) use ($ticket): void {
                            $query->where('model_type', app(TicketType::class)->getMorphClass())
                                ->where('model_id', $ticket->ticket_type_id);
                        });
                    });
            })
            ->get()
            ->toArray();

        $this->attachments = $ticket->media->toArray();

        $this->ticket = $ticket->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.portal.ticket.ticket');
    }

    #[Renderless]
    public function closeTicket(): void
    {
        try {
            UpdateTicket::make([
                'id' => $this->ticket['id'],
                'state' => Closed::class,
            ])
                ->validate()
                ->execute();

            $this->redirectRoute('portal.tickets', navigate: true);
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);
        }
    }

    public function escalateTicket(): void
    {
        try {
            UpdateTicket::make([
                'id' => $this->ticket['id'],
                'state' => Escalated::class,
            ])
                ->validate()
                ->execute();

            $this->ticket['state'] = Escalated::$name;
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);
        }
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('portal.ticket.comments')->text(__('Comments'))->isLivewireComponent(),
            TabButton::make('portal.ticket.activities')->text(__('Activities'))->isLivewireComponent(),
        ];
    }
}

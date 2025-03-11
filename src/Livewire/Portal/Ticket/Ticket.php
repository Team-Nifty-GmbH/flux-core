<?php

namespace FluxErp\Livewire\Portal\Ticket;

use FluxErp\Htmlables\TabButton;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Ticket as TicketModel;
use FluxErp\Models\TicketType;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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

    public function getTabs(): array
    {
        return [
            TabButton::make('portal.ticket.comments')->text(__('Comments'))->isLivewireComponent(),
            TabButton::make('portal.ticket.activities')->text(__('Activities'))->isLivewireComponent(),
        ];
    }
}

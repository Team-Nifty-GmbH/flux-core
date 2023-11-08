<?php

namespace FluxErp\Livewire\Portal\Ticket;

use FluxErp\Htmlables\TabButton;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\TicketType;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Ticket extends Component
{
    use WithTabs;

    public array $ticket;

    public array $additionalColumns = [];

    public array $attachments = [];

    public string $tab = 'features.comments.comments';

    public function mount(int $id): void
    {
        $ticket = \FluxErp\Models\Ticket::query()
            ->whereKey($id)
            ->where('authenticatable_type', Auth::user()->getMorphClass())
            ->where('authenticatable_id', Auth::id())
            ->firstOrFail();

        $this->additionalColumns = AdditionalColumn::query()
            ->where(function (Builder $query) use ($ticket) {
                $query->where('model_type', \FluxErp\Models\Ticket::class)
                    ->when($ticket->ticket_type_id, function (Builder $query) use ($ticket) {
                        $query->orWhere(function (Builder $query) use ($ticket) {
                            $query->where('model_type', TicketType::class)
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
            TabButton::make('features.comments.comments')->label(__('Comments')),
            TabButton::make('features.activities')->label(__('Activities')),
        ];
    }
}

<?php

namespace FluxErp\Livewire\Widgets;

use Carbon\Carbon;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Comment;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class TicketResponseTime extends Component
{
    use IsTimeFrameAwareWidget, Widgetable;

    public float|string|null $firstResponseHours = null;

    public float|string|null $resolutionHours = null;

    public string $firstResponseFormatted = '-';

    public string $resolutionFormatted = '-';

    public string $firstResponseColor = 'text-gray-700 dark:text-gray-300';

    public string $resolutionColor = 'text-gray-700 dark:text-gray-300';

    public static function getCategory(): ?string
    {
        return 'Tickets';
    }

    public static function getDefaultHeight(): int
    {
        return 1;
    }

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public function mount(): void
    {
        $this->calculateByTimeFrame();
    }

    public function render(): View|Factory
    {
        return view('flux::livewire.widgets.ticket-response-time');
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.box');
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateFirstResponseTime();
        $this->calculateResolutionTime();
    }

    protected function calculateFirstResponseTime(): void
    {
        $ticketMorph = morph_alias(Ticket::class);
        $userMorph = morph_alias(User::class);

        $tickets = resolve_static(Ticket::class, 'query')
            ->whereBetween('created_at', [
                $this->getStart()->toDateTimeString(),
                $this->getEnd()->toDateTimeString(),
            ])
            ->get(['id', 'created_at', 'created_by']);

        if ($tickets->isEmpty()) {
            $this->firstResponseHours = null;
            $this->firstResponseFormatted = '-';
            $this->firstResponseColor = 'text-gray-700 dark:text-gray-300';

            return;
        }

        $firstResponseDates = resolve_static(Comment::class, 'query')
            ->join('tickets', fn (JoinClause $join) => $join
                ->on('comments.model_id', '=', 'tickets.id')
                ->where('comments.model_type', $ticketMorph)
            )
            ->whereIntegerInRaw('tickets.id', $tickets->modelKeys())
            ->where('comments.created_by', 'LIKE', $userMorph . ':%')
            ->where(fn (Builder $subQuery) => $subQuery
                ->whereColumn('comments.created_by', '!=', 'tickets.created_by')
                ->orWhereNull('tickets.created_by')
            )
            ->groupBy('comments.model_id')
            ->selectRaw('comments.model_id, MIN(comments.created_at) as first_response_at')
            ->pluck('first_response_at', 'model_id');

        $totalHours = 0;
        $count = 0;

        foreach ($tickets as $ticket) {
            $firstResponseAt = $firstResponseDates->get($ticket->getKey());

            if ($firstResponseAt) {
                $totalHours = bcadd(
                    $totalHours,
                    bcdiv($ticket->created_at->diffInMinutes(Carbon::parse($firstResponseAt)), 60, 10),
                    10
                );
                $count++;
            }
        }

        if ($count === 0) {
            $this->firstResponseHours = null;
            $this->firstResponseFormatted = '-';
            $this->firstResponseColor = 'text-gray-700 dark:text-gray-300';

            return;
        }

        $this->firstResponseHours = bcround(bcdiv($totalHours, $count, 10), 1);
        $this->firstResponseFormatted = $this->formatHours($this->firstResponseHours);
        $this->firstResponseColor = $this->colorForHours($this->firstResponseHours);
    }

    protected function calculateResolutionTime(): void
    {
        $tickets = resolve_static(Ticket::class, 'query')
            ->withTrashed()
            ->whereNotNull('resolved_at')
            ->whereBetween('resolved_at', [
                $this->getStart()->toDateTimeString(),
                $this->getEnd()->toDateTimeString(),
            ])
            ->get(['id', 'created_at', 'resolved_at']);

        if ($tickets->isEmpty()) {
            $this->resolutionHours = null;
            $this->resolutionFormatted = '-';
            $this->resolutionColor = 'text-gray-700 dark:text-gray-300';

            return;
        }

        $totalHours = 0;

        foreach ($tickets as $ticket) {
            $totalHours = bcadd(
                $totalHours,
                bcdiv($ticket->created_at->diffInMinutes($ticket->resolved_at), 60, 10),
                10
            );
        }

        $this->resolutionHours = bcround(bcdiv($totalHours, $tickets->count(), 10), 1);
        $this->resolutionFormatted = $this->formatHours($this->resolutionHours);
        $this->resolutionColor = $this->colorForHours($this->resolutionHours, 48);
    }

    protected function formatHours(float|string $hours): string
    {
        if (bccomp($hours, 1, 10) < 0) {
            return bcround(bcmul($hours, 60, 10), 0) . 'm';
        }

        if (bccomp($hours, 24, 10) < 0) {
            return bcround($hours, 1) . 'h';
        }

        return bcround(bcdiv($hours, 24, 10), 1) . 'd';
    }

    protected function colorForHours(float|string $hours, float|string $redThreshold = 24): string
    {
        if (bccomp($hours, bcdiv($redThreshold, 3, 10), 10) <= 0) {
            return 'text-emerald-600 dark:text-emerald-400';
        }

        if (bccomp($hours, bcdiv(bcmul($redThreshold, 2, 10), 3, 10), 10) <= 0) {
            return 'text-amber-600 dark:text-amber-400';
        }

        return 'text-red-600 dark:text-red-400';
    }
}

<?php

namespace FluxErp\Livewire\Widgets;

use Carbon\Carbon;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Comment;
use FluxErp\Models\Ticket;
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

    public ?float $firstResponseHours = null;

    public ?float $resolutionHours = null;

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
                $totalHours += $ticket->created_at->diffInMinutes(Carbon::parse($firstResponseAt)) / 60;
                $count++;
            }
        }

        if ($count === 0) {
            $this->firstResponseHours = null;
            $this->firstResponseFormatted = '-';
            $this->firstResponseColor = 'text-gray-700 dark:text-gray-300';

            return;
        }

        $this->firstResponseHours = round($totalHours / $count, 1);
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
            $totalHours += $ticket->created_at->diffInMinutes($ticket->resolved_at) / 60;
        }

        $this->resolutionHours = round($totalHours / $tickets->count(), 1);
        $this->resolutionFormatted = $this->formatHours($this->resolutionHours);
        $this->resolutionColor = $this->colorForHours($this->resolutionHours, 48);
    }

    protected function formatHours(float $hours): string
    {
        if ($hours < 1) {
            return round($hours * 60) . 'm';
        }

        if ($hours < 24) {
            return round($hours, 1) . 'h';
        }

        return round($hours / 24, 1) . 'd';
    }

    protected function colorForHours(float $hours, float $redThreshold = 24): string
    {
        if ($hours <= $redThreshold / 3) {
            return 'text-emerald-600 dark:text-emerald-400';
        }

        if ($hours <= $redThreshold * 2 / 3) {
            return 'text-amber-600 dark:text-amber-400';
        }

        return 'text-red-600 dark:text-red-400';
    }
}

<?php

namespace FluxErp\Livewire\Widgets;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class MyLeadWonLostRatio extends OverallLeadWonLostRatio
{
    public ?int $userId = null;

    public function mount(): void
    {
        parent::mount();
        $this->userId = auth()->id();
    }

    protected function getBaseFilter(string $start, string $end): Closure
    {
        $userId = $this->userId;

        return function (Builder $query) use ($userId, $start, $end) {
            return $query
                ->whereNotNull('lead_state_id')
                ->where('user_id', $userId)
                ->whereBetween('closed_at', [$start, $end]);
        };
    }
}

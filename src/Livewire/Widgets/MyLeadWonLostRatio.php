<?php

namespace FluxErp\Livewire\Widgets;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class MyLeadWonLostRatio extends CompanyWideLeadWonLostRatio
{
    public ?int $userId = null;

    public function mount(): void
    {
        parent::mount();
        $this->userId = $this->userId ?? auth()->id();
    }

    protected function getBaseFilter(string $start, string $end): Closure
    {
        $userId = $this->userId;

        return function (Builder $query) use ($userId, $start, $end) {
            return $query
                ->where('user_id', $userId)
                ->whereNotNull('lead_state_id')
                ->whereBetween('created_at', [$start, $end]);
        };
    }
}

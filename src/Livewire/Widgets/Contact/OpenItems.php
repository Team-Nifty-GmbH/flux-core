<?php

namespace FluxErp\Livewire\Widgets\Contact;

use FluxErp\Livewire\Contact\Statistics;
use FluxErp\Livewire\Widgets\Outstanding;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class OpenItems extends Outstanding
{
    public ?int $contactId = null;

    public static function dashboardComponent(): array|string
    {
        return Statistics::class;
    }

    protected function getOutstandingQuery(Builder $query): Builder
    {
        return parent::getOutstandingQuery($query)
            ->where('contact_id', $this->contactId);
    }

    protected function getOverdueQuery(Builder $query): Builder
    {
        return parent::getOverdueQuery($query)
            ->where('contact_id', $this->contactId);
    }

    protected function liveUpdates(): array
    {
        return [
            $this->contactChannel($this->contactId) => [
                Order::class => ['updated', 'deleted'],
            ],
        ];
    }
}

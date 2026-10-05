<?php

namespace FluxErp\Livewire\Widgets\Contact;

use FluxErp\Livewire\Contact\Statistics;
use FluxErp\Livewire\Widgets\Outstanding;
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
}

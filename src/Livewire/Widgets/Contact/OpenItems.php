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

    protected function getOutstandingQuery(Builder $builder): Builder
    {
        return parent::getOutstandingQuery($builder)
            ->where('contact_id', $this->contactId);
    }

    protected function getOverdueQuery(Builder $builder): Builder
    {
        return parent::getOverdueQuery($builder)
            ->where('contact_id', $this->contactId);
    }
}

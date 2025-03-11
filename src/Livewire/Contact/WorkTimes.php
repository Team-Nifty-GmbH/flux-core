<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\DataTables\WorkTimeList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;

class WorkTimes extends WorkTimeList
{
    #[Modelable]
    public int $contactId;

    #[Renderless]
    public function getCacheKey(): string
    {
        return parent::getCacheKey() . $this->contactId;
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('contact_id', $this->contactId);
    }
}

<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\DataTables\WorkTimeList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class WorkTimes extends WorkTimeList
{
    #[Modelable]
    public int $contactId;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('contact_id', $this->contactId);
    }
}

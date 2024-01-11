<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\DataTables\ProjectList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class Projects extends ProjectList
{
    #[Modelable]
    public int $contactId;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('contact_id', $this->contactId);
    }
}

<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\DataTables\ProjectList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;

class Projects extends ProjectList
{
    #[Modelable]
    public int $contactId;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('contact_id', $this->contactId);
    }

    #[Renderless]
    public function getCacheKey(): string
    {
        return parent::getCacheKey() . $this->contactId;
    }
}

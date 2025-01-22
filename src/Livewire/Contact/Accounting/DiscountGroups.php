<?php

namespace FluxErp\Livewire\Contact\Accounting;

use FluxErp\Livewire\DataTables\DiscountGroupList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class DiscountGroups extends DiscountGroupList
{
    #[Modelable]
    public int $contactId;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->whereRelation(
            'contacts',
            'contacts.id',
            $this->contactId
        );
    }
}

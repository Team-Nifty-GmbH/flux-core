<?php

namespace FluxErp\Livewire\Resource;

use FluxErp\Livewire\DataTables\ResourceList as BaseResourceList;
use FluxErp\Livewire\Forms\ResourceForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;

class ResourceList extends BaseResourceList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public ResourceForm $resourceForm;

    protected ?string $includeBefore = 'flux::livewire.resource.resource-list';
}

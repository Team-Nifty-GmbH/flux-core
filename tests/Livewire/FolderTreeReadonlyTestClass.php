<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Contact;

class FolderTreeReadonlyTestClass extends FolderTree
{
    public bool $isReadonly = true;

    protected string $modelType = Contact::class;
}

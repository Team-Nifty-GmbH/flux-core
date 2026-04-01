<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Contact;

class FolderTreeTestClass extends FolderTree
{
    protected string $modelType = Contact::class;
}

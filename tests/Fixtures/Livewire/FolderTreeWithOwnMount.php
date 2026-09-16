<?php

namespace FluxErp\Tests\Fixtures\Livewire;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Contact;

class FolderTreeWithOwnMount extends FolderTree
{
    public ?string $address = null;

    protected string $modelType = Contact::class;

    public function mount(string $address, int $contactId): void
    {
        $this->address = $address;
        $this->modelId = $contactId;
    }
}

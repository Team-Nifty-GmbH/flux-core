<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Contact;
use FluxErp\Traits\Livewire\DataTable\AllowRecordMerging;

class ContactList extends BaseDataTable
{
    use AllowRecordMerging;

    public array $enabledCols = [
        'avatar',
        'customer_number',
        'main_address.company',
        'main_address.firstname',
        'main_address.lastname',
        'main_address.street',
        'main_address.zip',
        'main_address.city',
    ];

    public array $formatters = [
        'avatar' => 'image',
    ];

    public bool $isSelectable = true;

    protected string $model = Contact::class;

    protected function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['avatar'] = $item->getAvatarUrl();

        return $returnArray;
    }
}

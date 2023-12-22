<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Actions\Tag\DeleteTag;
use FluxErp\Actions\Tag\UpdateTag;

class TagForm extends FluxForm
{
    public ?int $id = null;

    public ?string $name = null;

    public ?string $slug = null;

    public ?string $type = null;

    public ?string $color = null;

    public ?int $order_column = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateTag::class,
            'update' => UpdateTag::class,
            'delete' => DeleteTag::class,
        ];
    }
}

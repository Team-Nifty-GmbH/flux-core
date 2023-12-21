<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Category\CreateCategory;
use FluxErp\Actions\Category\DeleteCategory;
use FluxErp\Actions\Category\UpdateCategory;
use Livewire\Attributes\Locked;

class CategoryForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $model_type = null;

    public ?int $parent_id;

    public ?string $name;

    public ?int $sort_number;

    public bool $is_active = true;

    protected function getActions(): array
    {
        return [
            'create' => CreateCategory::class,
            'update' => UpdateCategory::class,
            'delete' => DeleteCategory::class,
        ];
    }
}

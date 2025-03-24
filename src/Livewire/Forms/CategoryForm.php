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

    public bool $is_active = true;

    public ?string $model_type = null;

    public ?string $name;

    public ?int $parent_id;

    public ?int $sort_number;

    protected function getActions(): array
    {
        return [
            'create' => CreateCategory::class,
            'update' => UpdateCategory::class,
            'delete' => DeleteCategory::class,
        ];
    }
}

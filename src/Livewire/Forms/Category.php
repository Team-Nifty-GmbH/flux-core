<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Category\CreateCategory;
use FluxErp\Actions\Category\UpdateCategory;
use Livewire\Attributes\Locked;
use Livewire\Form;

class Category extends Form
{
    #[Locked]
    public ?int $id = null;

    public ?string $model_type = null;

    public ?int $parent_id;

    public ?string $name;

    public ?int $sort_number;

    public bool $is_active = true;

    public function save(): void
    {
        $action = $this->id ? UpdateCategory::make($this->toArray()) : CreateCategory::make($this->toArray());

        $response = $action->validate()->execute();

        $this->fill($response);
    }
}

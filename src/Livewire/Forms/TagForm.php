<?php

namespace FluxErp\Livewire\Forms;

use Livewire\Form;

class TagForm extends Form
{
    public ?int $id = null;

    public ?string $name = null;

    public ?string $slug = null;

    public ?string $type = null;

    public ?string $color = null;

    public ?int $order_column = null;

    public function save(): void
    {
        $action = $this->id ? UpdateTag::make($this->toArray()) : CreateTag::make($this->toArray());

        $response = $action->validate()->execute();

        $this->fill($response);
    }
}

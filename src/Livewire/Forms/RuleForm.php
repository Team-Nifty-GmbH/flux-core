<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Rule\CreateRule;
use FluxErp\Actions\Rule\DeleteRule;
use FluxErp\Actions\Rule\UpdateRule;
use Livewire\Attributes\Locked;

class RuleForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $description = null;

    public int $priority = 0;

    public bool $is_active = true;

    protected function getActions(): array
    {
        return [
            'create' => CreateRule::class,
            'update' => UpdateRule::class,
            'delete' => DeleteRule::class,
        ];
    }
}

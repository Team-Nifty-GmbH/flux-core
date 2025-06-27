<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Target\CreateTarget;
use FluxErp\Actions\Target\DeleteTarget;
use FluxErp\Actions\Target\UpdateTarget;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;

class TargetForm extends FluxForm
{
    public ?string $aggregate_column = null;

    public ?string $aggregate_type = null;

    public ?array $constraints = null;

    public ?string $end_date = null;

    #[Locked]
    public ?int $id = null;

    public ?string $model_type = null;

    public ?string $owner_column = null;

    public ?int $parent_id = null;

    public ?int $priority = null;

    public ?string $start_date = null;

    public ?string $target_value = null;

    public ?string $timeframe_column = null;

    public function modalName(): ?string
    {
        return Str::kebab(class_basename($this)) . '-modal';
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateTarget::class,
            'update' => UpdateTarget::class,
            'delete' => DeleteTarget::class,
        ];
    }
}

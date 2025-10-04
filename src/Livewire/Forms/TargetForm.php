<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Target\CreateTarget;
use FluxErp\Actions\Target\DeleteTarget;
use FluxErp\Actions\Target\UpdateTarget;
use FluxErp\Models\Target;
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

    public bool $is_group_target = false;

    public ?string $model_type = null;

    public ?string $name = null;

    public ?string $owner_column = null;

    public ?int $parent_id = null;

    public ?int $priority = null;

    public ?string $start_date = null;

    public ?string $target_value = null;

    public ?string $timeframe_column = null;

    public array $users = [];

    public function fill($values): void
    {
        if ($values instanceof Target) {
            $values->loadMissing('users');

            $values = $values->toArray();

            $values['users'] = array_map(function ($user) {
                return [
                    'user_id' => $user['id'],
                    'label' => $user['name'],
                    'target_share' => $user['pivot']['target_share'] ?? 0,
                    'is_percentage' => (bool) ($user['pivot']['is_percentage'] ?? true),
                ];
            }, $values['users'] ?? []);
        }

        parent::fill($values);
    }

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

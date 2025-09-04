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

    public array $user_shares = [];

    public array $users = [];

    public function fill($values): void
    {
        if ($values instanceof Target) {
            $model = $values->loadMissing('users');

            $userShares = [];
            $targetValue = ($model->target_value ?? 0);

            foreach ($model->users as $user) {
                $pivot = $user->pivot ?? null;

                $alloc = null;
                $abs = null;

                if ($pivot) {
                    $alloc = is_null($pivot->target_share) ? null : $pivot->target_share;
                }

                if ($alloc !== null) {
                    if ($targetValue > 0) {
                        $abs = bcround($alloc * $targetValue, 2);
                    }
                }

                $userShares[$user->id] = [
                    'relative' => bcround(bcmul($alloc, 100), 2),
                    'absolute' => $abs,
                ];
            }

            $arr = $model->toArray();
            $arr['users'] = $model->users->pluck('id')->all();
            $arr['user_shares'] = $userShares;

            parent::fill($arr);

            return;
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

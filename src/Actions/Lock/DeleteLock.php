<?php

namespace FluxErp\Actions\Lock;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\LockRecordRequest;
use FluxErp\Traits\Lockable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class DeleteLock extends FluxAction
{
    protected static bool $hasPermission = false;

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new LockRecordRequest())->rules();
    }

    public static function models(): array
    {
        return ModelInfo::forAllModels()
            ->merge(ModelInfo::forAllModels(flux_path('src/Models'), flux_path('src'), 'FluxErp'))
            ->filter(fn ($model) => in_array(Lockable::class, $model->traits->toArray()))
            ->map(fn ($model) => $model->class)
            ->toArray();
    }

    public function performAction(): bool
    {
        $model = $this->data['model_type']::query()
            ->whereKey($this->data['model_id'])
            ->first();

        return $model->getHasLockAttribute() || $model->unlock();
    }
}

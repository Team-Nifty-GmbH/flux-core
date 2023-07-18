<?php

namespace FluxErp\Actions\Locking;

use FluxErp\Actions\BaseAction;
use FluxErp\Rules\ClassExists;
use FluxErp\Traits\Lockable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class UnlockModel extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer',
            'model_type' => [
                'required',
                'string',
                new ClassExists(instanceOf: Model::class),
            ],
        ];
    }

    public static function models(): array
    {
        return array_values(
            ModelInfo::forAllModels()
                ->merge(
                    ModelInfo::forAllModels(
                        flux_path('src/Models'),
                        flux_path('src'), 'FluxErp'
                    )
                )
                ->filter(fn ($model) => in_array(Lockable::class, $model->traits->toArray()))
                ->map(fn ($model) => $model->class)
                ->toArray()
        );
    }

    public function execute()
    {
        return $this->data['model_type']::query()
            ->whereKey($this->data['id'])
            ->first()
            ->lock()
            ->delete();
    }

    public function validate(): static
    {
        parent::validate();

        if (! in_array(Lockable::class, class_uses($this->data['model_type']) ?: [])) {
            throw ValidationException::withMessages([
                'model' => [__('Model not lockable')],
            ])->errorBag('unlockModel');
        }

        if ($model = $this->data['model_type']::query()
            ->whereKey($this->data['id'])
            ->first()
        ) {
            throw ValidationException::withMessages([
                'id' => [__('Model instance not found')],
            ])->errorBag('unlockModel');
        }

        if ($model->lock && $model->lock->created_by !== Auth::id()) {
            throw ValidationException::withMessages([
                'model' => [__('Model is locked by another user')],
            ])->errorBag('unlockModel');
        }

        return $this;
    }
}

<?php

namespace FluxErp\Actions\Locking;

use FluxErp\Actions\BaseAction;
use FluxErp\Rules\ClassExists;
use FluxErp\Traits\Lockable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LockModel extends BaseAction
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
        return [];
    }

    public function execute(): bool
    {
        $model = $this->data['model_type']::query()
            ->whereKey($this->data['id'])
            ->first();

        if (! $model->lock()->exists()) {
            $model->lock()->create();
        }

        return true;
    }

    public function validate(): static
    {
        parent::validate();

        if (! in_array(Lockable::class, class_uses($this->data['model_type']) ?: [])) {
            throw ValidationException::withMessages([
                'model' => [__('Model not lockable')],
            ])->errorBag('lockModel');
        }

        if (! $this->data['model_type']::query()
            ->whereKey($this->data['id'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'id' => [__('Model instance not found')],
            ])->errorBag('lockModel');
        }

        if ($this->data['model_type']::query()
            ->whereKey($this->data['id'])
            ->whereRelation('lock', 'created_by', '!=', Auth::id())
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'model' => [__('Model is locked by another user')],
            ])->errorBag('lockModel');
        }

        return $this;
    }
}

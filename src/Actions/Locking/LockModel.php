<?php

namespace FluxErp\Actions\Locking;

use FluxErp\Actions\FluxAction;
use FluxErp\Rules\ClassExists;
use FluxErp\Traits\Lockable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LockModel extends FluxAction
{
    public static function models(): array
    {
        return [];
    }

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer',
            'model_type' => [
                'required',
                'string',
                new ClassExists(instanceOf: Model::class),
            ],
        ];
    }

    public function performAction(): true
    {
        $model = $this->data['model_type']::query()
            ->whereKey($this->data['id'])
            ->first();

        if (! $model->lock()->exists()) {
            $model->lock()->create();
        }

        return true;
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (! in_array(Lockable::class, class_uses($this->data['model_type']) ?: [])) {
            throw ValidationException::withMessages([
                'model' => ['Model not lockable'],
            ])->errorBag('lockModel');
        }

        if (! $this->data['model_type']::query()
            ->whereKey($this->data['id'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'id' => ['Model instance not found'],
            ])->errorBag('lockModel');
        }

        if ($this->data['model_type']::query()
            ->whereKey($this->data['id'])
            ->whereRelation('lock', 'created_by', '!=', Auth::id())
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'model' => ['Model is locked by another user'],
            ])->errorBag('lockModel');
        }
    }
}

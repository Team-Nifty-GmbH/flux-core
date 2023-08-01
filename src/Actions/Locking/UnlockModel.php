<?php

namespace FluxErp\Actions\Locking;

use FluxErp\Actions\FluxAction;
use FluxErp\Rules\ClassExists;
use FluxErp\Traits\Lockable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UnlockModel extends FluxAction
{
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

    public static function models(): array
    {
        return [];
    }

    public function performAction(): ?bool
    {
        return $this->data['model_type']::query()
            ->whereKey($this->data['id'])
            ->first()
            ->lock()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

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
    }
}

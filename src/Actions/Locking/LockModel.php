<?php

namespace FluxErp\Actions\Locking;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Rules\ClassExists;
use FluxErp\Traits\Lockable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class LockModel implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer',
            'model_type' => [
                'required',
                'string',
                new ClassExists(instanceOf: Model::class),
            ],
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'model.lock';
    }

    public static function description(): string|null
    {
        return 'lock model';
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

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

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

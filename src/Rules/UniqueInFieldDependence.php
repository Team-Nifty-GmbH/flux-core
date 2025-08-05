<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\DatabaseRule;

class UniqueInFieldDependence implements DataAwareRule, ValidationRule
{
    use DatabaseRule;

    protected array $data = [];

    protected array $dependingFields;

    protected bool $ignoreSelf;

    protected string $key;

    protected string $model;

    public function __construct(
        string $model,
        string|array $dependingField,
        bool $ignoreSelf = true,
        ?string $key = null
    ) {
        $this->dependingFields = is_array($dependingField) ? $dependingField : [$dependingField];
        $this->ignoreSelf = $ignoreSelf;
        $this->model = $model;
        $this->key = $key ?: (app($model))->getKeyName();
    }

    public function message(): string
    {
        return 'The %s has already been taken.';
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var Model $model */
        $model = app($this->model);

        $explodedDependingFields = [];
        foreach ($this->dependingFields as $dependingField) {
            $explodedDependingFields[] = explode('.', $dependingField);
        }

        $dependingFieldValues = [];
        foreach ($explodedDependingFields as $explodedDependingField) {
            $dependingFieldData = $this->data;

            foreach ($explodedDependingField as $field) {
                if (array_key_exists($field, $dependingFieldData)) {
                    $dependingFieldData = $dependingFieldData[$field];
                } else {
                    $dependingFieldData = $model->query()
                        ->select($field, $this->key)
                        ->where($this->key, $dependingFieldData[$this->key] ?? null)
                        ->first();

                    if (! $dependingFieldData || ! array_key_exists($field, $dependingFieldData->attributesToArray())) {
                        $fail(sprintf($this->message(), $attribute))->translate();

                        return;
                    }
                }
            }

            $dependingFieldValues[] = $dependingFieldData;
        }

        $explodedAttribute = explode('.', $attribute);

        $keyData = 0;
        if ($this->ignoreSelf) {
            $explodedKey = explode('.', $this->key);

            $keyData = $this->data;
            foreach ($explodedKey as $explodedValue) {
                if ($keyData[$explodedValue] ?? false) {
                    $keyData = $keyData[$explodedValue];
                } else {
                    $fail(sprintf($this->message(), $attribute))->translate();

                    return;
                }
            }
        }

        if ($model->query()
            ->where(array_pop($explodedAttribute), $value)
            ->where(function (Builder $query) use ($explodedDependingFields, $dependingFieldValues): void {
                foreach ($explodedDependingFields as $key => $explodedDependingField) {
                    $query->where(array_pop($explodedDependingField), $dependingFieldValues[$key]);
                }
            })
            ->when($this->ignoreSelf, function (Builder $query) use ($keyData): void {
                $query->whereKeyNot($keyData);
            })
            ->exists()
        ) {
            $fail(sprintf($this->message(), $attribute))->translate();
        }
    }
}

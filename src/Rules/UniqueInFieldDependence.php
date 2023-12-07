<?php

namespace FluxErp\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\DatabaseRule;

class UniqueInFieldDependence implements DataAwareRule, Rule
{
    use DatabaseRule;

    private array $data = [];

    private array $dependingFields;

    private bool $ignoreSelf;

    private string $model;

    private string $key;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $model, string|array $dependingField, bool $ignoreSelf = true, ?string $key = null)
    {
        $this->dependingFields = is_array($dependingField) ? $dependingField : [$dependingField];
        $this->ignoreSelf = $ignoreSelf;
        $this->model = $model;
        $this->key = $key ?: (new $model)->getKeyName();
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        /** @var Model $model */
        $model = $this->model;

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
                    $dependingFieldData = $model::query()
                        ->select($field, $this->key)
                        ->where($this->key, $dependingFieldData[$this->key] ?? null)
                        ->first();

                    if (! $dependingFieldData || ! array_key_exists($field, $dependingFieldData->attributesToArray())) {
                        return false;
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
                    return false;
                }
            }
        }

        return ! $model::query()
            ->where(array_pop($explodedAttribute), $value)
            ->where(function (Builder $query) use ($explodedDependingFields, $dependingFieldValues) {
                foreach ($explodedDependingFields as $key => $explodedDependingField) {
                    $query->where(array_pop($explodedDependingField), $dependingFieldValues[$key]);
                }
            })
            ->when($this->ignoreSelf, function (Builder $query) use ($keyData) {
                $query->whereKeyNot($keyData);
            })
            ->exists();
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The :attribute has already been taken.';
    }

    /**
     * @return $this|UniqueInFieldDependence
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }
}

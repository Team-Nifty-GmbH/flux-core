<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ModelExists extends Builder implements DataAwareRule, ValidationRule
{
    public bool $implicit = false;

    protected array $data;

    protected string $key;

    protected ?string $subject = null;

    protected string $subjectKeyName = 'id';

    public function __construct(
        Model|string $model,
        ?string $key = null,
        ?string $subject = null,
        ?string $subjectKeyName = null
    ) {
        if (is_string($model)) {
            $model = app($model);
        }

        $this->key = $key ?: $model->getQualifiedKeyName();
        $this->subject = $subject;
        $this->subjectKeyName = $subjectKeyName ?? $this->subjectKeyName;

        parent::__construct($model::query()->getQuery());

        $this->setModel($model);
        foreach ($model->getGlobalScopes() as $identifier => $scope) {
            $this->withGlobalScope($identifier, $scope);
        }
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_null($this->subject)
            && $this->subjectKeyName
            && ($subjectKey = data_get($this->data, $this->subjectKeyName))
            && (is_string($value) || is_int($value))
            && ($currentValue = resolve_static($this->subject, 'query')
                ->withoutGlobalScope(SoftDeletingScope::class)
                ->where($this->subjectKeyName, $subjectKey)
                ->toBase()
                ->value($attribute))
            && (string) $currentValue === (string) $value
        ) {
            return;
        }

        $query = $this->clone();
        if ($query->where($this->key, $value)->doesntExist()) {
            $fail('validation.exists')->translate();
        }
    }
}

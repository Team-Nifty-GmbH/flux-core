<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\Route;

class RouteExists implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    private ?string $parameterAttribute;

    public function __construct(?string $parameterAttribute = null)
    {
        $this->parameterAttribute = $parameterAttribute;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Route::has($value)) {
            $fail('The given route does not exist.')->translate();

            return;
        }

        if ($this->parameterAttribute) {
            try {
                route($value, data_get($this->data, $this->parameterAttribute) ?? []);
            } catch (UrlGenerationException) {
                $fail('The given route is missing required parameters.')->translate();
            }
        }
    }
}

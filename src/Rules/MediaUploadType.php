<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaUploadType implements DataAwareRule, ValidationRule
{
    protected array $data;

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $method = 'addMediaFrom' . ucfirst(strtolower($value));

        $model = $this->data['model_type'] ?? null;

        if (! $model) {
            $fail('model_type is not defined.')->translate();

            return;
        }

        $modelClass = morphed_model($model);
        if ($modelClass
            && $value
            && ! method_exists(app($modelClass), $method)
        ) {
            $fail(':input is not a valid :attribute.')->translate();

            return;
        }

        $valid = match (strtolower($value)) {
            'base64' => (bool) base64_decode($this->data['media']),
            'url' => Str::isUrl($this->data['media']),
            'string' => is_string($this->data['media']),
            'stream' => is_resource($this->data['media']),
            default => ($this->data['media'] ?? null) instanceof UploadedFile
                || (is_string($this->data['media'] ?? null) && file_exists($this->data['media'] ?? null)),
        };

        if (! $valid) {
            $fail(sprintf('Media is not a valid %s.', $value))->translate();
        }
    }
}

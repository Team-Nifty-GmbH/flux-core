<?php

namespace FluxErp\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaUploadType implements DataAwareRule, InvokableRule
{
    protected array $data;

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function __invoke($attribute, $value, $fail): void
    {
        $method = 'addMediaFrom' . ucfirst(strtolower($value));

        $model = $this->data['model_type'] ?? null;

        if (! $model) {
            $fail('model_type is not defined.')->translate();

            return;
        }

        $modelClass = Relation::getMorphedModel($model);
        if ($modelClass
            && $value
            && ! method_exists(app($modelClass), $method)
        ) {
            $fail(':input is not a valid :attribute.')->translate();
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

    /**
     * @return MediaUploadType|$this
     */
    public function setData($data): MediaUploadType|static
    {
        $this->data = $data;

        return $this;
    }
}

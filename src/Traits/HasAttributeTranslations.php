<?php

namespace FluxErp\Traits;

use FluxErp\Actions\AttributeTranslation\DeleteAttributeTranslation;
use FluxErp\Actions\AttributeTranslation\UpsertAttributeTranslation;
use FluxErp\Models\AttributeTranslation;
use FluxErp\Models\Language;
use FluxErp\Support\Collection\TranslatableCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

trait HasAttributeTranslations
{
    public ?array $translations = null;

    abstract protected function translatableAttributes(): array;

    protected static function bootHasAttributeTranslations(): void
    {
        static::saving(function (Model $model) {
            $languageId = Session::get('selectedLanguageId');

            if (is_null($languageId)
                || resolve_static(Language::class, 'default')?->id === $languageId
                || $model->translations !== null
            ) {
                return;
            }

            foreach ($model->translatableAttributes() as $translatableAttribute) {
                $value = $model->getAttribute($translatableAttribute);

                if (blank($value)) {
                    continue;
                }

                $model->translations[] = [
                    'language_id' => $languageId,
                    'attribute' => $translatableAttribute,
                    'value' => $value,
                ];

                $model->setAttribute($translatableAttribute, $model->getRawOriginal($translatableAttribute));
            }
        });

        static::saved(function (Model $model) {
            if (! $model->translations) {
                return;
            }

            foreach ($model->translations as $translation) {
                if (blank(data_get($translation, 'value'))
                    || data_get($translation, 'value') === '<p></p>'
                ) {
                    if ($attributeTranslationId = $model->attributeTranslations()
                        ->where('language_id', data_get($translation, 'language_id'))
                        ->where('attribute', data_get($translation, 'attribute'))
                        ->value('id')
                    ) {
                        DeleteAttributeTranslation::make([
                            'id' => $attributeTranslationId,
                        ])
                            ->validate()
                            ->execute();
                    }
                } else {
                    UpsertAttributeTranslation::make(
                        array_merge($translation, [
                            'model_type' => $model->getMorphClass(),
                            'model_id' => $model->getKey(),
                        ])
                    )
                        ->validate()
                        ->execute();
                }
            }
        });
    }

    public static function getTranslatableAttributes(): array
    {
        return app(static::class)->translatableAttributes();
    }

    public function isTranslatable(string $attribute): bool
    {
        return in_array($attribute, $this->getTranslatableAttributes());
    }

    public function getAttributeTranslation(string $attribute, int $languageId): string
    {
        return $this->attributeTranslations()
            ->where('language_id', $languageId)
            ->where('attribute', $attribute)
            ->value('value');
    }

    public function attributeTranslations(): MorphMany
    {
        return $this->morphMany(AttributeTranslation::class, 'model');
    }

    public function localize(?int $languageId = null): static
    {
        $languageId ??= Session::get('selectedLanguageId');

        if (is_null($languageId)) {
            return $this;
        }

        $translations = $this->attributeTranslations()
            ->where('language_id', $languageId)
            ->whereIn('attribute', $this->translatableAttributes())
            ->pluck('value', 'attribute')
            ->toArray();

        if ($translations) {
            $this->fill($translations);
        }

        return $this;
    }

    public function attributeTranslationRules(): array
    {
        return [
            'translations' => 'array|nullable',
            'translations.*' => 'required|array',
            'translations.*.language_id' => 'required|integer',
            'translations.*.attribute' => [
                'required',
                'string',
                Rule::in($this->translatableAttributes()),
            ],
            'translations.*.value' => 'string|nullable',
        ];
    }

    public function newCollection(array $models = []): Collection
    {
        return app(TranslatableCollection::class, ['items' => $models]);
    }
}

<?php

namespace FluxErp\Traits;

use FluxErp\Models\Language;
use Illuminate\Support\Arr;
use Spatie\Translatable\HasTranslations as BaseHasTranslations;

trait HasTranslations
{
    use BaseHasTranslations;

    public bool $hasAdditionalColumns;

    /**
     * Fill the model with an array of attributes.
     *
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \FluxErp\Exceptions\MetaException
     */
    public function fill(array $attributes): static
    {
        parent::fill($attributes);

        if ($attributes['locales'] ?? false) {
            $availableLocales = app(Language::class)->all()
                ->pluck('language_code')
                ->toArray();

            $attributes['locales'] = array_filter(
                (array) $attributes['locales'],
                function ($key) use ($availableLocales) {
                    return in_array($key, $availableLocales);
                },
                ARRAY_FILTER_USE_KEY
            );

            foreach ($attributes['locales'] as $localKey => $locale) {
                $locale = (array) $locale;

                $translatables = array_intersect_key(
                    $locale,
                    array_flip(
                        array_merge(
                            $this->translatable,
                            $this->hasAdditionalColumns ? $this->getTranslatableMeta() : []
                        )
                    )
                );
                foreach ($translatables as $key => $value) {
                    if ($this->isTranslatableAttribute($key)) {
                        $this->setTranslation($key, $localKey, $value);
                    } elseif ($this->hasAdditionalColumns && $this->isTranslatableMeta($key)) {
                        $this->setMetaTranslation($key, $localKey, $value);
                    }
                }
            }
        }

        return $this;
    }

    public function getLocale(): string
    {
        return app()->getLocale();
    }

    public function getTranslatableAttributes(): array
    {
        if (! property_exists($this, 'translatable')) {
            return [];
        }

        return is_array($this->translatable) ? $this->translatable : [];
    }

    /**
     * @throws \Spatie\Translatable\Exceptions\AttributeIsNotTranslatable
     */
    public function getTranslations(?string $key = null, ?array $allowedLocales = null): array
    {
        if ($key !== null) {
            $this->guardAgainstNonTranslatableAttribute($key);
            $value = json_decode($this->getAttributes()[$key] ?? '' ?: '{}', true);

            return array_filter(
                is_array($value) ? $value : [],
                fn ($value, $locale) => $this->filterTranslations($value, $locale, $allowedLocales),
                ARRAY_FILTER_USE_BOTH,
            );
        }

        return array_reduce($this->getTranslatableAttributes(), function ($result, $item) use ($allowedLocales) {
            $result[$item] = $this->getTranslations($item, $allowedLocales);

            return $result;
        });
    }

    public function hasTranslationsValidationRules(array $rules, ?array $data = null): array
    {
        $availableLocales = app(Language::class)->all()
            ->pluck('language_code')
            ->toArray();

        if (empty($availableLocales)) {
            $availableLocales = [config('app.locale')];
        }

        $setLocales = array_keys($data['locales'] ?? []);
        $availableLocales = array_intersect($availableLocales, $setLocales);
        $validationLocales = array_filter($availableLocales, function ($item) {
            return $item !== config('app.locale');
        });

        $translationRules = [];
        if (property_exists($this, 'translatable') && count($validationLocales) > 0) {
            $translatableRules = array_intersect_key(
                $rules,
                array_flip(
                    array_merge(
                        $this->translatable,
                        $this->hasAdditionalColumns ? $this->getTranslatableMeta() : []
                    )
                )
            );
            foreach ($validationLocales as $locale) {
                $localKey = 'locales.' . $locale . '.';
                $localRules = $translatableRules;

                $localRules = Arr::prependKeysWith($localRules, $localKey);
                $translationRules = array_merge($translationRules, $localRules);
            }
        }

        return $translationRules;
    }

    public function initializeHasTranslations(): void
    {
        $this->hasAdditionalColumns = in_array(HasAdditionalColumns::class, class_uses_recursive($this));
    }

    public function toArray(): array
    {
        $attributes = parent::toArray();
        foreach ($this->getTranslatableAttributes() as $field) {
            $attributes[$field] = $this->getTranslation(
                $field,
                app()->getLocale()
            ) ?: null;
        }

        return $attributes;
    }
}

<?php

namespace FluxErp\Services;

use FluxErp\Actions\Language\CreateLanguage;
use FluxErp\Actions\Language\DeleteLanguage;
use FluxErp\Actions\Language\UpdateLanguage;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Language;
use Illuminate\Validation\ValidationException;

class LanguageService
{
    public function create(array $data): Language
    {
        return CreateLanguage::make($data)->execute();
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = [];
        foreach ($data as $key => $item) {
            try {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 200,
                    data: $language = UpdateLanguage::make($item)->validate()->execute(),
                    additions: ['id' => $language->id]
                );
            } catch (ValidationException $e) {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: $e->errors(),
                    additions: [
                        'id' => array_key_exists('id', $item) ? $item['id'] : null,
                    ]
                );

                unset($data[$key]);
            }
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'language(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteLanguage::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'language deleted'
        );
    }

    public function initializeLanguages(): void
    {
        // create the default locale language
        $locale = Language::query()
            ->where('language_code', config('app.locale'))
            ->firstOrNew();
        if (! $locale->exists) {
            $locale->fill([
                'name' => config('app.locale'),
                'iso_name' => config('app.locale'),
                'language_code' => config('app.locale'),
            ]);
            $locale->save();
        }

        $fallback = Language::query()
            ->where('language_code', config('app.fallback_locale'))
            ->firstOrNew();
        if (! $fallback->exists) {
            $fallback->fill([
                'name' => config('app.fallback_locale'),
                'iso_name' => config('app.fallback_locale'),
                'language_code' => config('app.fallback_locale'),
            ]);
            $fallback->save();
        }

        $path = resource_path() . '/init-files/languages.json';
        $json = json_decode(file_get_contents($path), true);

        if ($json['model'] === 'Language') {
            $jsonLanguages = $json['data'];

            if ($jsonLanguages) {
                foreach ($jsonLanguages as $jsonLanguage) {
                    $jsonLanguage['name'] = __($jsonLanguage['name']);

                    // Save to database.
                    $language = Language::query()
                        ->where('language_code', $jsonLanguage['language_code'])
                        ->firstOrNew();

                    if (! $language->exists) {
                        $language->fill($jsonLanguage);
                        $language->save();
                    }
                }
            }
        }
    }
}

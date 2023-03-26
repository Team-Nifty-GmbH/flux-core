<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateLanguageRequest;
use FluxErp\Models\Language;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LanguageService
{
    public function create(array $data): Language
    {
        $language = new Language($data);
        $language->save();

        return $language;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateLanguageRequest(),
            model: new Language()
        );

        foreach ($data as $item) {
            // Find existing data to update.
            $language = Language::query()
                ->whereKey($item['id'])
                ->first();

            // Save new data to table.
            $language->fill($item);
            $language->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $language->withoutRelations()->fresh(),
                additions: ['id' => $language->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'languages updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $language = Language::query()
            ->whereKey($id)
            ->first();

        if (! $language) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'language not found']
            );
        }

        // Don't delete if in use.
        if ($language->addresses()->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['address' => 'language referenced by an address']
            );
        }

        if ($language->users()->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['user' => 'language referenced by a user']
            );
        }

        $language->delete();

        // Rename unique columns on soft-delete.
        $language->language_code = $language->language_code . '___' . Hash::make(Str::uuid());
        $language->save();

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

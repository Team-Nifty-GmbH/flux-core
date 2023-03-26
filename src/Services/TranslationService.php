<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateTranslationRequest;
use Spatie\TranslationLoader\LanguageLine;

class TranslationService
{
    public function create(array $data): LanguageLine
    {
        return LanguageLine::create($data);
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateTranslationRequest()
        );

        foreach ($data as $item) {
            $languageLine = LanguageLine::query()
                ->whereKey($item['id'])
                ->first();

            $languageLine->fill($item);
            $languageLine->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $languageLine,
                additions: ['id' => $languageLine->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'language line(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $languageLine = LanguageLine::query()
            ->whereKey($id)
            ->first();

        if (! $languageLine) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'language line not found']
            );
        }

        $languageLine->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'language line deleted'
        );
    }
}

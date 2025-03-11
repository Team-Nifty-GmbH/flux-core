<?php

namespace FluxErp\Services;

use FluxErp\Actions\Translation\CreateTranslation;
use FluxErp\Actions\Translation\DeleteTranslation;
use FluxErp\Actions\Translation\UpdateTranslation;
use FluxErp\Helpers\ResponseHelper;
use Illuminate\Validation\ValidationException;
use Spatie\TranslationLoader\LanguageLine;

class TranslationService
{
    public function create(array $data): LanguageLine
    {
        return CreateTranslation::make($data)->validate()->execute();
    }

    public function delete(string $id): array
    {
        try {
            DeleteTranslation::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'language line deleted'
        );
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
                    data: $languageLine = UpdateTranslation::make($item)->validate()->execute(),
                    additions: ['id' => $languageLine->id]
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
            statusMessage: $statusCode === 422 ? null : 'language line(s) updated',
            bulk: true
        );
    }
}

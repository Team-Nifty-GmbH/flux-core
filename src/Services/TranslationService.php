<?php

namespace FluxErp\Services;

use FluxErp\Actions\Translation\CreateLanguageLine;
use FluxErp\Actions\Translation\DeleteLanguageLine;
use FluxErp\Actions\Translation\UpdateLanguageLine;
use FluxErp\Helpers\ResponseHelper;
use Illuminate\Validation\ValidationException;
use Spatie\TranslationLoader\LanguageLine;

class TranslationService
{
    public function create(array $data): LanguageLine
    {
        return CreateLanguageLine::make($data)->execute();
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
                    data: $languageLine = UpdateLanguageLine::make($item)->validate()->execute(),
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

    public function delete(string $id): array
    {
        try {
            DeleteLanguageLine::make(['id' => $id])->validate()->execute();
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
}

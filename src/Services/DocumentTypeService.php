<?php

namespace FluxErp\Services;

use FluxErp\Actions\DocumentType\CreateDocumentType;
use FluxErp\Actions\DocumentType\DeleteDocumentType;
use FluxErp\Actions\DocumentType\UpdateDocumentType;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\DocumentType;
use Illuminate\Validation\ValidationException;

class DocumentTypeService
{
    public function create(array $data): DocumentType
    {
        return CreateDocumentType::make($data)->execute();
    }

    public function delete(string $id): array
    {
        try {
            DeleteDocumentType::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'document type deleted'
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
                    data: $documentType = UpdateDocumentType::make($item)->validate()->execute(),
                    additions: ['id' => $documentType->id]
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
            statusMessage: $statusCode === 422 ? null : 'document type(s) updated',
            bulk: true
        );
    }
}

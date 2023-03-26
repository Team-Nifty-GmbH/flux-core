<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateDocumentTypeRequest;
use FluxErp\Models\DocumentType;

class DocumentTypeService
{
    public function create(array $data): DocumentType
    {
        $documentType = new DocumentType($data);
        $documentType->save();

        return $documentType;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateDocumentTypeRequest(),
            model: new DocumentType()
        );

        foreach ($data as $item) {
            $documentType = DocumentType::query()
                ->whereKey($item['id'])
                ->first();

            // Save new data to table.
            $documentType->fill($item);
            $documentType->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $documentType->withoutRelations()->fresh(),
                additions: ['id' => $documentType->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'document types updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $documentType = DocumentType::query()
            ->whereKey($id)
            ->first();

        if (! $documentType) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'document type not found']
            );
        }

        $documentType->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'document type deleted'
        );
    }
}

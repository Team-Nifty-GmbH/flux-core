<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateContactOptionRequest;
use FluxErp\Models\ContactOption;

class ContactOptionService
{
    public function create(array $data): ContactOption
    {
        $contactOption = new ContactOption($data);
        $contactOption->save();

        return $contactOption;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateContactOptionRequest()
        );

        foreach ($data as $item) {
            $contactOption = ContactOption::query()
                ->whereKey($item['id'])
                ->first();

            $contactOption->fill($item);
            $contactOption->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $contactOption->withoutRelations()->fresh(),
                additions: ['id' => $contactOption->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'contact option(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $contactOption = ContactOption::query()
            ->whereKey($id)
            ->first();

        if (! $contactOption) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'contact option not found']
            );
        }

        $contactOption->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'contact option deleted'
        );
    }
}

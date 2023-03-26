<?php

namespace FluxErp\Services;

use FluxErp\Helpers\Helper;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateValueListRequest;
use FluxErp\Models\AdditionalColumn;

class ValueListService
{
    public function create(array $data): array
    {
        if (! array_is_list($data['values'])) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: ['values' => 'values array is no list']
            );
        }

        $modelClass = Helper::classExists(classString: ucfirst($data['model_type']), isModel: true);
        if (! $modelClass) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['model_type' => 'model type not found']
            );
        }

        if (AdditionalColumn::query()
            ->where('name', $data['name'])
            ->where('model_type', $modelClass)
            ->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 409,
                data: ['name_model' => 'Name model combination already exists']
            );
        }

        // Save data to table.
        $valueList = new AdditionalColumn();
        $valueList->name = $data['name'];
        $valueList->model_type = $modelClass;
        $valueList->values = $data['values'];
        $valueList->save();

        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: $valueList,
            statusMessage: 'Value list created'
        );
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateValueListRequest(),
            service: $this
        );

        foreach ($data as $item) {
            $valueList = AdditionalColumn::query()
                ->whereKey($item['id'])
                ->first();

            $valueList->fill($item);
            $valueList->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $valueList->withoutRelations()->fresh(),
                additions: ['id' => $valueList->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'value lists updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $valueList = AdditionalColumn::query()
            ->whereKey($id)
            ->whereNotNull('values')
            ->first();

        if (! $valueList) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'value list not found']
            );
        }

        if ($valueList->modelValues()->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['model_has_values' => 'value list referenced by at least one model instance']
            );
        }

        $valueList->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'value list deleted'
        );
    }

    public function validateItem(array $item, array $response): ?array
    {
        $valueList = AdditionalColumn::query()
            ->whereKey($item['id'])
            ->first();

        if ($item['values'] ?? false) {
            if (! array_is_list($item['values'])) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: ['values' => 'values array is no list']
                );
            } elseif ($valueList->modelValues()->whereNotIn('meta.value', $item['values'])->exists()) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 409,
                    data: ['values' => 'Models with differing values exist'],
                    additions: $response
                );
            }
        }

        $item['name'] = $item['name'] ?? $valueList->name;
        $item['model_type'] = $item['model_type'] ?? $valueList->model_type;

        if (AdditionalColumn::query()
            ->where('id', '!=', $item['id'])
            ->where('name', $item['name'])
            ->where('model_type', $item['model_type'])
            ->exists()
        ) {
            return ResponseHelper::createArrayResponse(
                statusCode: 409,
                data: ['name_model' => 'Name model combination already exists'],
                additions: $response
            );
        }

        return null;
    }
}

<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateSerialNumberRangeRequest;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;

class SerialNumberRangeService
{
    public function create(array $data): SerialNumberRange
    {
        $data['current_number'] = array_key_exists('start_number', $data) ?
            --$data['start_number'] : 0;
        unset($data['start_number']);

        $serialNumberRange = new SerialNumberRange($data);
        $serialNumberRange->save();

        return $serialNumberRange;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateSerialNumberRangeRequest(),
            service: $this
        );

        foreach ($data as $item) {
            $serialNumberRange = SerialNumberRange::query()
                ->whereKey($item['id'])
                ->first();

            $serialNumberRange->fill($item);
            $serialNumberRange->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $serialNumberRange->withoutRelations()->fresh(),
                additions: ['id' => $serialNumberRange->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'serial number ranges updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $serialNumberRange = SerialNumberRange::query()
            ->whereKey($id)
            ->first();

        if (! $serialNumberRange) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'serial number range not found']
            );
        }

        $serialNumberRange->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'serial number range deleted'
        );
    }

    public function validateItem(array $item, array $response): ?array
    {
        if (array_key_exists('prefix', $item) || array_key_exists('affix', $item)) {
            if (SerialNumber::query()->where('serial_number_range_id', $item['id'])->exists()) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 423,
                    data: ['serial_numbers' => 'serial number range has serial numbers'],
                    additions: $response
                );
            }
        }

        return null;
    }
}

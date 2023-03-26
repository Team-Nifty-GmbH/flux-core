<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateTicketTypeRequest;
use FluxErp\Models\TicketType;

class TicketTypeService
{
    public function create(array $data): TicketType
    {
        $ticketType = new TicketType($data);
        $ticketType->save();

        return $ticketType;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateTicketTypeRequest()
        );

        foreach ($data as $item) {
            $ticketType = TicketType::query()
                ->whereKey($item['id'])
                ->first();

            $ticketType->fill($item);
            $ticketType->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $ticketType->withoutRelations()->fresh(),
                additions: ['id' => $ticketType->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'ticket type updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $ticketType = TicketType::query()
            ->whereKey($id)
            ->first();

        if (! $ticketType) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'ticket type not found']
            );
        }

        if ($ticketType->tickets()->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['address' => 'ticket type has tickets']
            );
        }

        $ticketType->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'ticket type deleted'
        );
    }
}

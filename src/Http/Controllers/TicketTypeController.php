<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateTicketTypeRequest;
use FluxErp\Models\TicketType;
use FluxErp\Services\TicketTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketTypeController extends BaseController
{
    public function __construct(?string $permission = null)
    {
        parent::__construct($permission);
        $this->model = new TicketType();
    }

    public function create(CreateTicketTypeRequest $request, TicketTypeService $ticketTypeService): JsonResponse
    {
        $ticketType = $ticketTypeService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $ticketType,
            statusMessage: 'ticket type created'
        );
    }

    public function update(Request $request, TicketTypeService $ticketTypeService): JsonResponse
    {
        $response = $ticketTypeService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, TicketTypeService $ticketTypeService): JsonResponse
    {
        $response = $ticketTypeService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}

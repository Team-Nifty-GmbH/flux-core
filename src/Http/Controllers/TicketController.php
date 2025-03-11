<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Ticket;
use FluxErp\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Ticket::class);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, TicketService $ticketService): JsonResponse
    {
        $ticket = $ticketService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $ticket,
            statusMessage: 'ticket created'
        );
    }

    public function delete(string $id, TicketService $ticketService): JsonResponse
    {
        $response = $ticketService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function toggleUserAssignment(Request $request, TicketService $ticketService): JsonResponse
    {
        $response = $ticketService->toggleUserAssignment($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, TicketService $ticketService): JsonResponse
    {
        $ticket = $ticketService->update($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $ticket,
            statusMessage: 'ticket updated'
        );
    }
}

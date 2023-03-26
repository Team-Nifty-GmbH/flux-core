<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateTicketRequest;
use FluxErp\Http\Requests\ToggleTicketUserAssignmentRequest;
use FluxErp\Http\Requests\UpdateTicketRequest;
use FluxErp\Models\Ticket;
use FluxErp\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TicketController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Ticket();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(CreateTicketRequest $request, TicketService $ticketService): JsonResponse
    {
        $validator = Validator::make($request->all(), (new CreateTicketRequest())->rules());
        $validator->addModel($this->model);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $validator->errors()->toArray()
            );
        }

        $ticket = $ticketService->create($validator->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $ticket,
            statusMessage: 'ticket created'
        );
    }

    public function update(UpdateTicketRequest $request, TicketService $ticketService): JsonResponse
    {
        $ticket = $ticketService->update($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $ticket,
            statusMessage: 'ticket updated'
        );
    }

    public function delete(string $id, TicketService $ticketService): JsonResponse
    {
        $response = $ticketService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function toggleUserAssignment(ToggleTicketUserAssignmentRequest $request,
        TicketService $ticketService): JsonResponse
    {
        $response = $ticketService->toggleUserAssignment($request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}

<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Contact;
use FluxErp\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Contact::class);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, ContactService $contactService): JsonResponse
    {
        $contact = $contactService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $contact,
            statusMessage: 'contact created'
        );
    }

    public function delete(string $id, ContactService $contactService): JsonResponse
    {
        $response = $contactService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, ContactService $contactService): JsonResponse
    {
        $response = $contactService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}

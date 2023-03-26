<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateContactRequest;
use FluxErp\Models\Contact;
use FluxErp\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Contact();
    }

    /**
     * @param CreateContactRequest $request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, ContactService $contactService): JsonResponse
    {
        $validator = Validator::make($request->all(), (new CreateContactRequest())->rules());
        $validator->addModel($this->model);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $validator->errors()->toArray()
            );
        }

        $contact = $contactService->create($validator->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $contact,
            statusMessage: 'contact created'
        );
    }

    public function update(Request $request, ContactService $contactService): JsonResponse
    {
        $response = $contactService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, ContactService $contactService): JsonResponse
    {
        $response = $contactService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}

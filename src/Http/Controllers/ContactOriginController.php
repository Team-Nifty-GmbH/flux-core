<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\ContactOrigin\CreateContactOrigin;
use FluxErp\Actions\ContactOrigin\DeleteContactOrigin;
use FluxErp\Actions\ContactOrigin\UpdateContactOrigin;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\ContactOrigin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContactOriginController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(ContactOrigin::class);
    }

    public function create(Request $request): JsonResponse
    {
        $contactOrigin = CreateContactOrigin::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $contactOrigin,
            statusMessage: 'contact origin created'
        );
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->all();
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = [];
        foreach ($data as $key => $item) {
            try {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 200,
                    data: $contactOrigin = UpdateContactOrigin::make($item)->validate()->execute(),
                    additions: ['id' => $contactOrigin->id]
                );
            } catch (ValidationException $e) {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: $e->errors(),
                    additions: [
                        'id' => array_key_exists('id', $item) ? $item['id'] : null,
                    ]
                );

                unset($data[$key]);
            }
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createResponseFromArrayResponse([
            'status' => $statusCode,
            'responses' => $responses,
            'statusMessage' => $statusCode === 422 ? null : 'contact origin(s) updated',
        ]);
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeleteContactOrigin::make(['id' => $id])->validate()->execute();
            $response = ResponseHelper::createArrayResponse(
                statusCode: 204,
                statusMessage: 'contact origin deleted'
            );
        } catch (ValidationException $e) {
            $response = ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}

<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\MailAccount\CreateMailAccount;
use FluxErp\Actions\MailAccount\DeleteMailAccount;
use FluxErp\Actions\MailAccount\UpdateMailAccount;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateMailAccountRequest;
use FluxErp\Models\MailAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MailAccountController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new MailAccount();
    }

    public function create(CreateMailAccountRequest $request): JsonResponse
    {
        $mailAccount = CreateMailAccount::make($request->all())
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $mailAccount,
            statusMessage: 'mail account created'
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
                    data: $mailAccount = UpdateMailAccount::make($item)->validate()->execute(),
                    additions: ['id' => $mailAccount->id]
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
            'statusMessage' => $statusCode === 422 ? null : 'mail account(s) updated',
        ]);
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeleteMailAccount::make(['id' => $id])->validate()->execute();
            $response = ResponseHelper::createArrayResponse(
                statusCode: 204,
                statusMessage: 'mail account deleted'
            );
        } catch (ValidationException $e) {
            $response = ResponseHelper::createArrayResponse(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}

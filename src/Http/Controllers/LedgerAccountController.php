<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\LedgerAccount\CreateLedgerAccount;
use FluxErp\Actions\LedgerAccount\DeleteLedgerAccount;
use FluxErp\Actions\LedgerAccount\UpdateLedgerAccount;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateLedgerAccountRequest;
use FluxErp\Models\LedgerAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LedgerAccountController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new LedgerAccount();
    }

    public function create(CreateLedgerAccountRequest $request): JsonResponse
    {
        $ledgerAccount = CreateLedgerAccount::make($request->validated())
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $ledgerAccount,
            statusMessage: 'ledger account created'
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
                    data: $ledgerAccount = UpdateLedgerAccount::make($item)->validate()->execute(),
                    additions: ['id' => $ledgerAccount->id]
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
            'statusMessage' => $statusCode === 422 ? null : 'ledger account(s) updated',
        ]);
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeleteLedgerAccount::make(['id' => $id])->validate()->execute();
            $response = ResponseHelper::createArrayResponse(
                statusCode: 204,
                statusMessage: 'ledger account deleted'
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

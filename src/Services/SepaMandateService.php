<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateSepaMandateRequest;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\SepaMandate;

class SepaMandateService
{
    public function create(array $data): array
    {
        $clientContactExists = Contact::query()
            ->whereKey($data['contact_id'])
            ->where('client_id', $data['client_id'])
            ->exists();

        if (! $clientContactExists) {
            return ResponseHelper::createArrayResponse(
                statusCode: 409,
                data: ['contact_id' => 'client has not such contact']
            );
        }

        $contactBankConnectionExists = BankConnection::query()
            ->whereKey($data['bank_connection_id'])
            ->where('contact_id', $data['contact_id'])
            ->exists();

        if (! $contactBankConnectionExists) {
            return ResponseHelper::createArrayResponse(
                statusCode: 409,
                data: ['bank_connection_id' => 'contact has no such bank connection']
            );
        }

        $sepaMandate = new SepaMandate($data);
        $sepaMandate->save();

        return ResponseHelper::createArrayResponse(statusCode: 201, data: $sepaMandate);
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateSepaMandateRequest(),
            service: $this
        );

        foreach ($data as $item) {
            $sepaMandate = SepaMandate::query()
                ->whereKey($item['id'])
                ->first();

            $sepaMandate->fill($item);
            $sepaMandate->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $sepaMandate->withoutRelations()->fresh(),
                additions: ['id' => $sepaMandate->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'sepa mandates updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $sepaMandate = SepaMandate::query()
            ->whereKey($id)
            ->first();

        if (! $sepaMandate) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'sepa mandate not found']
            );
        }

        $sepaMandate->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'sepa mandate deleted'
        );
    }

    public function validateItem(array $item, array $response): ?array
    {
        $sepaMandate = SepaMandate::query()
            ->whereKey($item['id'])
            ->first();

        $item['contact_id'] = $item['contact_id'] ?? $sepaMandate->contact_id;
        $item['client_id'] = $item['client_id'] ?? $sepaMandate->client_id;
        $item['bank_connection_id'] = $item['bank_connection_id'] ??
            $sepaMandate->bank_connection_id;

        $clientContactExists = Contact::query()
            ->whereKey($item['contact_id'])
            ->where('client_id', $item['client_id'])
            ->exists();

        $contactBankConnectionExists = BankConnection::query()
            ->whereKey($item['bank_connection_id'])
            ->where('contact_id', $item['contact_id'])
            ->exists();

        $errors = [];
        if (! $clientContactExists) {
            $errors += [
                'contact_id' => 'contact with id: \'' . $item['contact_id'] .
                    '\' doesnt match client id:\'' . $item['client_id'] . '\'',
            ];
        }

        if (! $contactBankConnectionExists) {
            $errors += [
                'bank_connection_id' => 'bank connection with id: \'' .
                    $item['bank_connection_id'] . '\' doesnt match contact id:' . $item['contact_id'] . '\'',
            ];
        }

        if (! $clientContactExists || ! $contactBankConnectionExists) {
            return ResponseHelper::createArrayResponse(
                statusCode: 409,
                data: $errors,
                additions: $response
            );
        }

        return null;
    }
}

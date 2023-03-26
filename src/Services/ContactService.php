<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateContactRequest;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;

class ContactService
{
    public function create(array $data): Contact
    {
        $data['customer_number'] = $data['customer_number'] ?? uniqid();

        $contact = new Contact($data);
        $contact->save();

        return $contact;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateContactRequest(),
            service: $this,
            model: new Contact()
        );

        foreach ($data as $item) {
            $contact = Contact::query()
                ->whereKey($item['id'])
                ->first();

            $contact->fill($item);
            $contact->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $contact->withoutRelations()->fresh(),
                additions: ['id' => $contact->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'contacts updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $contact = Contact::query()
            ->whereKey($id)
            ->first();

        if (! $contact) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'contact not found']
            );
        }

        $contact->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'contact deleted'
        );
    }

    public function validateItem(array $item, array $response): ?array
    {
        $contact = Contact::query()
            ->whereKey($item['id'])
            ->first();

        $item['payment_type_id'] = $item['payment_type_id'] ?? $contact->payment_type_id;
        $item['client_id'] = $item['client_id'] ?? $contact->client_id;

        if (array_key_exists('customer_number', $item)) {
            $customerNumberExists = Contact::query()
                ->where('id', '!=', $item['id'])
                ->where('client_id', '=', $item['client_id'])
                ->where('customer_number', $item['customer_number'])
                ->exists();

            if ($customerNumberExists) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 409,
                    data: ['customer_number' => 'customer number already exists'],
                    additions: $response
                );
            }
        }

        $clientPaymentTypeExists = PaymentType::query()
            ->whereKey($item['payment_type_id'])
            ->where('client_id', $item['client_id'])
            ->exists();

        if (! $clientPaymentTypeExists) {
            return ResponseHelper::createArrayResponse(
                statusCode: 409,
                data: [
                    'payment_type_id' => 'payment type with id: \'' . $item['payment_type_id'] .
                        '\' doesnt match client id:\'' . $item['client_id'] . '\'',
                ],
                additions: $response
            );
        }

        return null;
    }
}

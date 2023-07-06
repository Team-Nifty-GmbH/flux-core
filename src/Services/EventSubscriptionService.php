<?php

namespace FluxErp\Services;

use FluxErp\Actions\EventSubscription\CreateEventSubscription;
use FluxErp\Actions\EventSubscription\DeleteEventSubscription;
use FluxErp\Actions\EventSubscription\UpdateEventSubscription;
use FluxErp\Helpers\ResponseHelper;
use Illuminate\Validation\ValidationException;

class EventSubscriptionService
{
    public function create(array $data): array
    {
        try {
            $subscription = CreateEventSubscription::make($data)->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: $subscription,
            statusMessage: 'subscription created'
        );
    }

    public function update(array $data): array
    {
        try {
            $subscription = UpdateEventSubscription::make($data)->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            data: $subscription,
            statusMessage: 'subscription updated'
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteEventSubscription::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'subscription deleted'
        );
    }
}

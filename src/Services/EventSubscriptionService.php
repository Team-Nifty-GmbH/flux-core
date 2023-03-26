<?php

namespace FluxErp\Services;

use FluxErp\Helpers\Helper;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\EventSubscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class EventSubscriptionService
{
    public function create(array $data): array
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id();

        $data = $this->validateData($data);
        if (array_key_exists('status', $data)) {
            return $data;
        }

        $subscription = new EventSubscription($data);
        $subscription->save();

        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: $subscription,
            statusMessage: 'subscription created'
        );
    }

    public function update(array $data): array
    {
        $subscription = EventSubscription::query()
            ->whereKey($data['id'])
            ->where('user_id', Auth::id())
            ->first();

        if (! $subscription) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'subscription not found']
            );
        }

        $data = $this->validateData($data, $subscription);
        if (array_key_exists('status', $data)) {
            return $data;
        }

        $subscription->fill($data);
        $subscription->save();

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            data: $subscription,
            statusMessage: 'subscription updated'
        );
    }

    public function delete(string $id): array
    {
        $subscription = EventSubscription::query()
            ->whereKey($id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $subscription) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'subscription not found']
            );
        }

        $subscription->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'subscription deleted'
        );
    }

    private function validateData(array $data, Model $subscription = null): array
    {
        $eventClass = Helper::classExists(classString: ucfirst($data['event']), isEvent: true);

        if ($data['event'] !== '*' && ! $eventClass) {
            $eventExploded = explode(':', str_replace(' ', '', $data['event']));
            $model = $eventExploded[1] ?? null;
            $eloquentEvent = $model ? eloquent_model_event($eventExploded[0], $model) : null;
        } else {
            $eloquentEvent = $data['event'];
        }

        if (! $eventClass && ! $eloquentEvent) {
            return ResponseHelper::createArrayResponse(statusCode: 404, data: ['event' => 'event not found']);
        }

        try {
            /** @var Model $modelClass */
            $modelClass = ModelInfo::forModel($data['model_type'])->class;
        } catch (\Throwable $e) {
            return ResponseHelper::createArrayResponse(statusCode: 404, data: ['model_type' => 'model type not found']);
        }

        if ($data['model_id']) {
            if (! $modelClass::query()->whereKey($data['model_id'])->exists()) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 404,
                    data: ['model_id' => 'model instance not found']
                );
            }
        }

        if (! $this->checkSubscription(
            userId: $data['user_id'] ?? Auth::id(),
            event: $eventClass ?: $eloquentEvent,
            modelType: $modelClass,
            modelId: $data['model_id'],
            subscriptionId: ! is_null($subscription) ? $subscription->id : null
        )) {
            return ResponseHelper::createArrayResponse(statusCode: 409, data: ['subscription' => 'already subscribed']);
        }

        return array_merge(
            $data,
            [
                'model_type' => $modelClass,
                'event' => $eventClass ?: $eloquentEvent,
            ]
        );
    }

    private function checkSubscription(int $userId, string $event, string $modelType,
        ?int $modelId, int $subscriptionId = null): bool
    {
        $subscriptions = EventSubscription::query()
            ->where('event', $event)
            ->where('user_id', $userId)
            ->where('model_type', $modelType)
            ->when($subscriptionId, function ($query, $subscriptionId) {
                return $query->whereKeyNot($subscriptionId);
            })
            ->get();

        if ($subscriptions->contains('model_id', '=', null)) {
            return false;
        }

        return ! empty($modelId) && ! $subscriptions->contains('model_id', '=', $modelId);
    }
}

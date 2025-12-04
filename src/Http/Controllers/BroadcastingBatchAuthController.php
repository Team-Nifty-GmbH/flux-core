<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Http\Requests\BroadcastingBatchAuthRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use JsonSerializable;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class BroadcastingBatchAuthController
{
    public function __invoke(BroadcastingBatchAuthRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $results = [];

        foreach ($validated['channels'] as $channel) {
            $channelName = $channel['name'];
            $channelSocketId = $channel['socket_id'] ?? $validated['socket_id'];

            try {
                $subRequest = Request::create(
                    '/broadcasting/auth',
                    'POST',
                    [
                        'channel_name' => $channelName,
                        'socket_id' => $channelSocketId,
                    ]
                );
                $subRequest->setUserResolver($request->getUserResolver());

                $response = Broadcast::auth($subRequest);

                $results[$channelName] = match (true) {
                    is_array($response) => $response,
                    is_string($response) => json_decode($response, true) ?? [],
                    $response instanceof JsonSerializable => $response->jsonSerialize(),
                    default => [],
                };
            } catch (Throwable $e) {
                $results[$channelName] = [
                    'status' => $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500,
                ];
            }
        }

        $errors = array_filter(array_column($results, 'status'));

        $statusCode = match (true) {
            $errors === [] => 200,
            count($errors) === count($results) => max($errors),
            default => 207,
        };

        return response()->json($results, $statusCode);
    }
}

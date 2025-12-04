<?php

namespace FluxErp\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Throwable;

class BroadcastingBatchAuthController
{
    public function __invoke(Request $request): JsonResponse
    {
        $channels = $request->input('channels', []);
        $socketId = $request->input('socket_id');
        $results = [];

        foreach ($channels as $channel) {
            $channelName = data_get($channel, 'name');
            $channelSocketId = data_get($channel, 'socket_id', $socketId);

            if (! $channelName) {
                continue;
            }

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

                $results[$channelName] = $response;
            } catch (Throwable) {
                $results[$channelName] = ['error' => true];
            }
        }

        return response()->json($results);
    }
}

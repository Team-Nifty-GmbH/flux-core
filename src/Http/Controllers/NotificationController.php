<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\MarkNotificationsReadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function userIndex(Request $request): JsonResponse
    {
        $user = $request->user();

        $page = max((int) $request->page, 1);
        $perPage = $request->per_page > 500 || $request->per_page < 1 ? 25 : $request->per_page;

        $notifications = $user->notifications()
            ->paginate(perPage: $perPage, page: $page)
            ->through(fn ($notification): array => [
                'id' => $notification->getKey(),
                'title' => data_get($notification->data, 'title'),
                'description' => data_get($notification->data, 'description'),
                'type' => data_get($notification->data, 'toastType'),
                'url' => data_get($notification->data, 'accept.url'),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ])
            ->appends($request->query());

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $notifications,
            additions: ['unread_count' => $user->unreadNotifications()->count()],
        )
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function markRead(MarkNotificationsReadRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        if (data_get($validated, 'all')) {
            $user->unreadNotifications()->update(['read_at' => now()]);
        } elseif (! blank(data_get($validated, 'id'))) {
            $user->notifications()
                ->whereKey(data_get($validated, 'id'))
                ->first()
                ?->markAsRead();
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: ['unread_count' => $user->unreadNotifications()->count()],
        );
    }
}

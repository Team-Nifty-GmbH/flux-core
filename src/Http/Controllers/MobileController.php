<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\DeviceToken\DeleteDeviceToken;
use FluxErp\Http\Requests\DeleteDeviceTokenRequest;
use FluxErp\Http\Requests\LoginMobileRequest;
use FluxErp\Listeners\RegisterMobilePushToken;
use FluxErp\Models\DeviceToken;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Throwable;

class MobileController extends Controller
{
    public function loginMobile(LoginMobileRequest $request): Redirector|RedirectResponse
    {
        if ($request->has('fcm_token')) {
            $sessionData = [
                'pending_fcm_token' => $request->input('fcm_token'),
                'pending_fcm_platform' => $request->input('platform', 'ios'),
                'pending_fcm_device_id' => $request->input('device_id'),
                'pending_fcm_device_name' => $request->input('device_name'),
                'pending_fcm_device_model' => $request->input('device_model'),
                'pending_fcm_device_manufacturer' => $request->input('device_manufacturer'),
                'pending_fcm_device_os_version' => $request->input('device_os_version'),
            ];

            if (auth()->check()) {
                try {
                    session($sessionData);

                    app(RegisterMobilePushToken::class)
                        ->handle(new Login(guard: 'web', user: auth()->user(), remember: false));
                } catch (Throwable $e) {
                    report($e);
                }
            } else {
                session($sessionData);
            }
        }

        if ($request->has('redirect')) {
            $redirectPath = $request->input('redirect');

            if (auth()->check()) {
                return redirect($redirectPath);
            }

            session(['url.intended' => url($redirectPath)]);
        }

        return redirect('/');
    }

    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function config(): JsonResponse
    {
        return response()->json([
            'app_name' => config('app.name'),
        ]);
    }

    public function deleteDeviceToken(DeleteDeviceTokenRequest $request): JsonResponse
    {
        $deviceToken = resolve_static(DeviceToken::class, 'query')
            ->where('device_id', $request->validated('device_id'))
            ->first();

        if (! $deviceToken) {
            return response()->json([
                'success' => false,
                'error' => 'Device token not found',
            ], 404);
        }

        try {
            DeleteDeviceToken::make(['id' => $deviceToken->getKey()])
                ->validate()
                ->execute();

            return response()->json([
                'success' => true,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

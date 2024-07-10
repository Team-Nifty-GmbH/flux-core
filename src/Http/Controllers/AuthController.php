<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Address;
use FluxErp\Models\InterfaceUser;
use FluxErp\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @unauthenticated
     */
    public function authenticate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:username|string',
            'username' => 'required_without:email|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(statusCode: 422, data: $validator->errors()->toArray());
        }

        $abilities = [];

        $user = app(User::class)->query()
            ->where('email', $request->email ?? $request->username)
            ->where('is_active', true)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $abilities = ['user'];
        }

        if (count($abilities) < 1) {
            $user = app(Address::class)->query()
                ->where('email', $request->email)
                ->where('can_login', true)
                ->whereNotNull('email')
                ->whereNotNull('password')
                ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                $abilities = ['address'];
            }
        }

        if (count($abilities) < 1) {
            $user = app(InterfaceUser::class)->query()
                ->where('name', $request->email)
                ->where('is_active', true)
                ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                $abilities = ['interface'];
            }
        }

        if (count($abilities) < 1) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 401,
                data: ['credentials' => 'invalid credentials']
            );
        }

        $token = $user->createToken('API Token', $abilities);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: null,
            additions: [
                'access_token' => $token->plainTextToken,
                'token' => $token->plainTextToken,
            ]
        );
    }

    public function authenticateWeb(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('web')->attempt(array_merge($credentials, ['is_active' => true]))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    public function authenticatePortal(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::guard('address')->attempt(
            array_merge($credentials, [
                'is_active' => true,
                'can_login' => true,
            ])
        )) {
            $request->session()->regenerate();

            return redirect()->intended(route('portal.dashboard'));
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        $request->user()->locks()->delete();

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            statusMessage: 'user logged out'
        );
    }

    public function validateToken(): JsonResponse
    {
        return response()->json(['status' => 'token valid']);
    }
}

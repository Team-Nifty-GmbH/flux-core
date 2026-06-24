<?php

namespace FluxErp\Http\Controllers;

use Closure;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

        $user = resolve_static(User::class, 'query')
            ->where('email', $request->email ?? $request->username)
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 401,
                data: ['credentials' => 'invalid credentials']
            );
        }

        $token = $user->createToken('API Token', ['user']);

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

    public function loginUrl(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'redirect' => ['nullable', 'string', function (string $attribute, mixed $value, Closure $fail): void {
                if (! blank($value) && ! Route::has($value)) {
                    $fail(__('The :attribute must be a valid route name.', ['attribute' => $attribute]));
                }
            }],
            'redirect_params' => ['nullable', 'array'],
        ]);

        $intended = null;
        if (! blank($validated['redirect'] ?? null)) {
            try {
                $intended = route($validated['redirect'], $validated['redirect_params'] ?? []);
            } catch (UrlGenerationException) {
                throw ValidationException::withMessages([
                    'redirect' => __('The :attribute route parameters are invalid.', ['attribute' => 'redirect']),
                ]);
            }
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: ['url' => $request->user()->generateLoginLink($intended)],
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

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

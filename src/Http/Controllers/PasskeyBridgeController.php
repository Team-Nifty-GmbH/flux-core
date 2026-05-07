<?php

namespace FluxErp\Http\Controllers;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;
use Spatie\LaravelPasskeys\Support\Config as PasskeyConfig;
use Throwable;

class PasskeyBridgeController extends Controller
{
    protected const KIND_LOGIN = 'login';

    protected const KIND_REGISTER = 'register';

    protected const TTL_MINUTES = 5;

    protected const STATE_PREFIX = 'passkey_bridge_state_';

    protected const TRANSFER_PREFIX = 'passkey_bridge_transfer_';

    public function showLogin(Request $request): View
    {
        $validated = $this->validateBridgeRequest($request);

        $action = PasskeyConfig::getAction(
            'generate_passkey_authentication_options',
            GeneratePasskeyAuthenticationOptionsAction::class
        );
        $optionsJson = $action->execute();

        $code = Str::random(64);
        $this->putState($code, [
            'kind' => self::KIND_LOGIN,
            'challenge' => $validated['code_challenge'],
            'redirect_uri' => $validated['redirect_uri'],
            'options' => $optionsJson,
            'user_type' => null,
            'user_id' => null,
        ]);

        return view('flux::passkey-bridge.login', [
            'code' => $code,
            'options' => $optionsJson,
            'finishUrl' => route('passkey-bridge.login.finish'),
            'cancelRedirect' => $validated['redirect_uri'] . '?error=cancelled',
        ]);
    }

    public function finishLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:64'],
            'response' => ['required', 'string'],
        ]);

        $state = $this->getState($validated['code']);

        if (! $state || $state['kind'] !== self::KIND_LOGIN || ! is_null($state['user_id'])) {
            return response()->json(['error' => 'invalid_bridge_code'], 400);
        }

        $action = PasskeyConfig::getAction('find_passkey', FindPasskeyToAuthenticateAction::class);
        $passkey = $action->execute($validated['response'], $state['options']);

        if (! $passkey || ! $passkey->authenticatable) {
            return response()->json(['error' => 'invalid_passkey'], 401);
        }

        $this->putState($validated['code'], [
            ...$state,
            'options' => null,
            'user_type' => $passkey->getAttribute('authenticatable_type'),
            'user_id' => $passkey->getAttribute('authenticatable_id'),
        ]);

        return response()->json([
            'redirect' => $this->buildRedirect($state['redirect_uri'], $validated['code']),
        ]);
    }

    public function startRegistration(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $validated = $this->validateBridgeRequest($request);

        $action = PasskeyConfig::getAction(
            'generate_passkey_register_options',
            GeneratePasskeyRegisterOptionsAction::class
        );
        $optionsJson = $action->execute($user);

        $code = Str::random(64);
        $transferToken = Str::random(64);

        $this->putState($code, [
            'kind' => self::KIND_REGISTER,
            'challenge' => $validated['code_challenge'],
            'redirect_uri' => $validated['redirect_uri'],
            'options' => $optionsJson,
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getKey(),
        ]);
        Cache::put(
            self::TRANSFER_PREFIX . $transferToken,
            $code,
            now()->addMinutes(self::TTL_MINUTES)
        );

        return response()->json([
            'bridge_url' => route('passkey-bridge.register.show', [
                'transfer_token' => $transferToken,
            ]),
        ]);
    }

    public function showRegister(Request $request): View
    {
        $validated = $request->validate([
            'transfer_token' => ['required', 'string', 'size:64'],
        ]);

        $code = Cache::get(self::TRANSFER_PREFIX . $validated['transfer_token']);
        $state = $code ? $this->getState($code) : null;

        abort_unless(
            $state && $state['kind'] === self::KIND_REGISTER && ! is_null($state['user_id']),
            404
        );

        return view('flux::passkey-bridge.register', [
            'code' => $code,
            'transferToken' => $validated['transfer_token'],
            'options' => $state['options'],
            'finishUrl' => route('passkey-bridge.register.finish'),
            'cancelRedirect' => $state['redirect_uri'] . '?error=cancelled',
        ]);
    }

    public function finishRegister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:64'],
            'transfer_token' => ['required', 'string', 'size:64'],
            'name' => ['required', 'string', 'max:255'],
            'response' => ['required', 'string'],
        ]);

        $mappedCode = Cache::get(self::TRANSFER_PREFIX . $validated['transfer_token']);
        $state = $this->getState($validated['code']);

        if (
            $mappedCode !== $validated['code']
            || ! $state
            || $state['kind'] !== self::KIND_REGISTER
            || is_null($state['user_id'])
        ) {
            return response()->json(['error' => 'invalid_bridge_code'], 400);
        }

        $authenticatable = $this->resolveUser($state);

        if (! $authenticatable) {
            return response()->json(['error' => 'invalid_bridge_code'], 400);
        }

        $action = PasskeyConfig::getAction('store_passkey', StorePasskeyAction::class);

        try {
            $action->execute(
                $authenticatable,
                $validated['response'],
                $state['options'],
                $request->getHost(),
                ['name' => $validated['name']],
            );
        } catch (Throwable) {
            return response()->json(['error' => 'invalid_passkey'], 422);
        }

        Cache::forget(self::TRANSFER_PREFIX . $validated['transfer_token']);
        $this->putState($validated['code'], [...$state, 'options' => null]);

        return response()->json([
            'redirect' => $this->buildRedirect($state['redirect_uri'], $validated['code']),
        ]);
    }

    public function exchange(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:64'],
            'code_verifier' => ['required', 'string', 'min:43', 'max:128'],
        ]);

        $state = Cache::pull(self::STATE_PREFIX . $validated['code']);

        if (! $state || is_null($state['user_id'])) {
            return response()->json(['error' => 'invalid_code'], 400);
        }

        $expectedChallenge = $this->base64UrlEncode(
            hash('sha256', $validated['code_verifier'], true)
        );

        if (! hash_equals($state['challenge'], $expectedChallenge)) {
            return response()->json(['error' => 'invalid_verifier'], 400);
        }

        if ($state['kind'] === self::KIND_REGISTER) {
            return response()->json(['status' => 'ok']);
        }

        return response()->json([
            'magic_login_url' => $this->generateMagicLoginUrl($state),
        ]);
    }

    protected function validateBridgeRequest(Request $request): array
    {
        return $request->validate([
            'code_challenge' => ['required', 'string', 'size:43'],
            'redirect_uri' => ['required', 'string', 'max:255', $this->redirectUriRule()],
        ]);
    }

    protected function redirectUriRule(): Closure
    {
        return function (string $attribute, string $value, Closure $fail): void {
            $scheme = parse_url($value, PHP_URL_SCHEME);
            $allowed = config(
                'flux.passkey_bridge.allowed_redirect_schemes',
                ['nuxbe']
            );

            if (! is_string($scheme) || ! in_array($scheme, $allowed, true)) {
                $fail('The :attribute scheme is not permitted.');
            }
        };
    }

    protected function buildRedirect(string $redirectUri, string $code): string
    {
        $separator = str_contains($redirectUri, '?') ? '&' : '?';

        return $redirectUri . $separator . 'code=' . urlencode($code);
    }

    protected function generateMagicLoginUrl(array $state): string
    {
        $token = Str::uuid()->toString();
        $expires = now()->addMinutes(self::TTL_MINUTES);

        Cache::put(
            'login_token_' . $token,
            [
                'user_type' => $state['user_type'],
                'user_id' => $state['user_id'],
                'guard' => 'web',
                'intended_url' => route('dashboard'),
            ],
            $expires
        );

        return URL::temporarySignedRoute('login-link', $expires, ['token' => $token]);
    }

    protected function putState(string $code, array $state): void
    {
        Cache::put(
            self::STATE_PREFIX . $code,
            $state,
            now()->addMinutes(self::TTL_MINUTES)
        );
    }

    protected function getState(string $code): ?array
    {
        return Cache::get(self::STATE_PREFIX . $code);
    }

    protected function resolveUser(array $state): ?object
    {
        if (! $state['user_type'] || ! $state['user_id']) {
            return null;
        }

        return morphed_model($state['user_type'])::query()
            ->whereKey($state['user_id'])
            ->first();
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

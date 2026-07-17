<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\PasskeyBridgeExchangeRequest;
use FluxErp\Http\Requests\PasskeyBridgeFinishLoginRequest;
use FluxErp\Http\Requests\PasskeyBridgeFinishRegisterRequest;
use FluxErp\Http\Requests\PasskeyBridgeShowRegisterRequest;
use FluxErp\Http\Requests\PasskeyBridgeStartRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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

    public function showLogin(PasskeyBridgeStartRequest $request): View
    {
        $validated = $request->validated();

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

    public function finishLogin(PasskeyBridgeFinishLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $state = $this->getState($validated['code']);

        if (! $state || $state['kind'] !== self::KIND_LOGIN || ! is_null($state['user_id'])) {
            return ResponseHelper::unprocessableEntity('invalid_bridge_code');
        }

        $action = PasskeyConfig::getAction('find_passkey', FindPasskeyToAuthenticateAction::class);
        $passkey = $action->execute($validated['response'], $state['options']);

        if (! $passkey || ! $passkey->authenticatable) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 401,
                statusMessage: 'passkey_authentication_failed',
            );
        }

        $this->putState($validated['code'], [
            ...$state,
            'options' => null,
            'user_type' => $passkey->getAttribute('authenticatable_type'),
            'user_id' => $passkey->getAttribute('authenticatable_id'),
        ]);

        return ResponseHelper::ok(
            statusMessage: 'ok',
            data: [
                'redirect' => $this->buildRedirect($state['redirect_uri'], $validated['code']),
            ],
        );
    }

    public function startRegistration(PasskeyBridgeStartRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 401,
                statusMessage: 'unauthenticated',
            );
        }

        $validated = $request->validated();

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

        return ResponseHelper::ok(
            statusMessage: 'ok',
            data: [
                'bridge_url' => route('passkey-bridge.register.show', [
                    'transfer_token' => $transferToken,
                ]),
            ],
        );
    }

    public function showRegister(PasskeyBridgeShowRegisterRequest $request): View
    {
        $validated = $request->validated();

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

    public function finishRegister(PasskeyBridgeFinishRegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $mappedCode = Cache::get(self::TRANSFER_PREFIX . $validated['transfer_token']);
        $state = $this->getState($validated['code']);

        if (
            $mappedCode !== $validated['code']
            || ! $state
            || $state['kind'] !== self::KIND_REGISTER
            || is_null($state['user_id'])
        ) {
            return ResponseHelper::unprocessableEntity('invalid_bridge_code');
        }

        $authenticatable = $this->resolveUser($state);

        if (! $authenticatable) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 401,
                statusMessage: 'authenticatable_not_found',
            );
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
            return ResponseHelper::unprocessableEntity('invalid_passkey');
        }

        Cache::forget(self::TRANSFER_PREFIX . $validated['transfer_token']);
        $this->putState($validated['code'], [...$state, 'options' => null]);

        return ResponseHelper::ok(
            statusMessage: 'ok',
            data: [
                'redirect' => $this->buildRedirect($state['redirect_uri'], $validated['code']),
            ],
        );
    }

    public function exchange(PasskeyBridgeExchangeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $state = Cache::pull(self::STATE_PREFIX . $validated['code']);

        if (! $state || is_null($state['user_id'])) {
            return ResponseHelper::unprocessableEntity('invalid_code');
        }

        $expectedChallenge = $this->base64UrlEncode(
            hash('sha256', $validated['code_verifier'], true)
        );

        if (! hash_equals($state['challenge'], $expectedChallenge)) {
            return ResponseHelper::unprocessableEntity('invalid_verifier');
        }

        if ($state['kind'] === self::KIND_REGISTER) {
            return ResponseHelper::ok(statusMessage: 'ok');
        }

        return ResponseHelper::ok(
            statusMessage: 'ok',
            data: [
                'magic_login_url' => $this->generateMagicLoginUrl($state),
            ],
        );
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

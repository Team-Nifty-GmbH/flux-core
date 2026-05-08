<?php

use FluxErp\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;
use Spatie\LaravelPasskeys\Models\Passkey;

const STATE_PREFIX = 'passkey_bridge_state_';
const TRANSFER_PREFIX = 'passkey_bridge_transfer_';

function pkceVerifier(): string
{
    return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
}

function pkceChallenge(string $verifier): string
{
    return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
}

function defaultLoginState(string $challenge, ?User $user = null): array
{
    return [
        'kind' => 'login',
        'challenge' => $challenge,
        'redirect_uri' => 'nuxbe://auth-callback',
        'options' => '{"challenge":"abc"}',
        'user_type' => $user?->getMorphClass(),
        'user_id' => $user?->getKey(),
    ];
}

function defaultRegisterState(string $challenge, User $user): array
{
    return [
        'kind' => 'register',
        'challenge' => $challenge,
        'redirect_uri' => 'nuxbe://auth-callback',
        'options' => '{}',
        'user_type' => $user->getMorphClass(),
        'user_id' => $user->getKey(),
    ];
}

beforeEach(function (): void {
    config(['passkeys.actions.generate_passkey_authentication_options' => FakeAuthOptionsAction::class]);
    config(['passkeys.actions.generate_passkey_register_options' => FakeRegisterOptionsAction::class]);
});

test('showLogin stores a login state in cache and renders the page', function (): void {
    $challenge = pkceChallenge(pkceVerifier());

    $response = $this->get(route('passkey-bridge.login.show', [
        'code_challenge' => $challenge,
        'redirect_uri' => 'nuxbe://auth-callback',
    ]));

    $response->assertOk();

    $code = Str::of(
        collect(Cache::store()->getStore()->all())
            ->keys()
            ->first(fn (string $key) => str_starts_with($key, STATE_PREFIX))
    )->after(STATE_PREFIX)->value();

    $state = Cache::get(STATE_PREFIX . $code);
    expect($state['kind'])->toBe('login')
        ->and($state['challenge'])->toBe($challenge)
        ->and($state['redirect_uri'])->toBe('nuxbe://auth-callback')
        ->and($state['user_id'])->toBeNull();
});

test('showLogin rejects a redirect_uri scheme that is not allowed', function (): void {
    $response = $this->getJson(route('passkey-bridge.login.show', [
        'code_challenge' => pkceChallenge(pkceVerifier()),
        'redirect_uri' => 'https://evil.example.com/callback',
    ]));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('redirect_uri');
});

test('showLogin rejects a code_challenge with the wrong length', function (): void {
    $response = $this->getJson(route('passkey-bridge.login.show', [
        'code_challenge' => 'too-short',
        'redirect_uri' => 'nuxbe://auth-callback',
    ]));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('code_challenge');
});

test('finishLogin attaches the authenticated user and returns the redirect', function (): void {
    config(['passkeys.actions.find_passkey' => FakeFindPasskeyAction::class]);
    FakeFindPasskeyAction::$user = $this->user;

    $code = Str::random(64);
    Cache::put(STATE_PREFIX . $code, defaultLoginState(pkceChallenge(pkceVerifier())), now()->addMinutes(5));

    $response = $this->postJson(route('passkey-bridge.login.finish'), [
        'code' => $code,
        'response' => json_encode(['type' => 'public-key']),
    ]);

    $response->assertOk();
    expect($response->json('redirect'))->toStartWith('nuxbe://auth-callback?code=');

    $state = Cache::get(STATE_PREFIX . $code);
    expect($state)->not->toBeNull()
        ->and($state['user_id'])->toBe($this->user->getKey())
        ->and($state['options'])->toBeNull();
});

test('finishLogin rejects an unknown code', function (): void {
    $response = $this->postJson(route('passkey-bridge.login.finish'), [
        'code' => str_repeat('x', 64),
        'response' => json_encode(['type' => 'public-key']),
    ]);

    $response->assertStatus(400)->assertJson(['error' => 'invalid_bridge_code']);
});

test('finishLogin rejects a state that is already attached to a user', function (): void {
    config(['passkeys.actions.find_passkey' => FakeFindPasskeyAction::class]);
    FakeFindPasskeyAction::$user = $this->user;

    $code = Str::random(64);
    Cache::put(
        STATE_PREFIX . $code,
        defaultLoginState(pkceChallenge(pkceVerifier()), $this->user),
        now()->addMinutes(5)
    );

    $response = $this->postJson(route('passkey-bridge.login.finish'), [
        'code' => $code,
        'response' => json_encode(['type' => 'public-key']),
    ]);

    $response->assertStatus(400)->assertJson(['error' => 'invalid_bridge_code']);
});

test('finishLogin returns 401 when no passkey matches', function (): void {
    config(['passkeys.actions.find_passkey' => FakeFindPasskeyAction::class]);
    FakeFindPasskeyAction::$user = null;

    $code = Str::random(64);
    Cache::put(STATE_PREFIX . $code, defaultLoginState(pkceChallenge(pkceVerifier())), now()->addMinutes(5));

    $response = $this->postJson(route('passkey-bridge.login.finish'), [
        'code' => $code,
        'response' => json_encode(['type' => 'public-key']),
    ]);

    $response->assertStatus(401)->assertJson(['error' => 'invalid_passkey']);
});

test('startRegistration requires authentication', function (): void {
    $this->actingAsGuest();

    $response = $this->postJson(route('passkey-bridge.start-registration'), [
        'code_challenge' => pkceChallenge(pkceVerifier()),
        'redirect_uri' => 'nuxbe://auth-callback',
    ]);

    $response->assertStatus(401);
});

test('startRegistration stores a register state and returns a transfer-tokenised bridge URL', function (): void {
    $this->actingAs($this->user, 'web');

    $verifier = pkceVerifier();
    $challenge = pkceChallenge($verifier);

    $response = $this->postJson(route('passkey-bridge.start-registration'), [
        'code_challenge' => $challenge,
        'redirect_uri' => 'nuxbe://auth-callback',
    ]);

    $response->assertOk();

    parse_str(parse_url($response->json('bridge_url'), PHP_URL_QUERY), $query);
    $transferToken = $query['transfer_token'] ?? null;
    expect($transferToken)->not->toBeNull();

    $code = Cache::get(TRANSFER_PREFIX . $transferToken);
    $state = Cache::get(STATE_PREFIX . $code);

    expect($state['kind'])->toBe('register')
        ->and($state['user_id'])->toBe($this->user->getKey())
        ->and($state['challenge'])->toBe($challenge);
});

test('showRegister returns 404 for an unknown transfer_token', function (): void {
    $response = $this->get(route('passkey-bridge.register.show', [
        'transfer_token' => str_repeat('x', 64),
    ]));

    $response->assertNotFound();
});

test('showRegister renders when transfer_token maps to a usable register state', function (): void {
    $code = Str::random(64);
    $transferToken = Str::random(64);
    Cache::put(STATE_PREFIX . $code, defaultRegisterState(pkceChallenge(pkceVerifier()), $this->user), now()->addMinutes(5));
    Cache::put(TRANSFER_PREFIX . $transferToken, $code, now()->addMinutes(5));

    $response = $this->get(route('passkey-bridge.register.show', ['transfer_token' => $transferToken]));

    $response->assertOk();
});

test('finishRegister stores the passkey via the configured action', function (): void {
    config(['passkeys.actions.store_passkey' => FakeStorePasskeyAction::class]);
    FakeStorePasskeyAction::$called = false;

    $code = Str::random(64);
    $transferToken = Str::random(64);
    Cache::put(STATE_PREFIX . $code, defaultRegisterState(pkceChallenge(pkceVerifier()), $this->user), now()->addMinutes(5));
    Cache::put(TRANSFER_PREFIX . $transferToken, $code, now()->addMinutes(5));

    $response = $this->postJson(route('passkey-bridge.register.finish'), [
        'code' => $code,
        'transfer_token' => $transferToken,
        'name' => 'iPhone',
        'response' => json_encode(['type' => 'public-key']),
    ]);

    $response->assertOk();
    expect(FakeStorePasskeyAction::$called)->toBeTrue()
        ->and(Cache::get(TRANSFER_PREFIX . $transferToken))->toBeNull()
        ->and(Cache::get(STATE_PREFIX . $code)['options'])->toBeNull();
});

test('finishRegister rejects when transfer_token does not map to the supplied code', function (): void {
    $code = Str::random(64);
    Cache::put(STATE_PREFIX . $code, defaultRegisterState(pkceChallenge(pkceVerifier()), $this->user), now()->addMinutes(5));

    $response = $this->postJson(route('passkey-bridge.register.finish'), [
        'code' => $code,
        'transfer_token' => str_repeat('x', 64),
        'name' => 'iPhone',
        'response' => json_encode(['type' => 'public-key']),
    ]);

    $response->assertStatus(400)->assertJson(['error' => 'invalid_bridge_code']);
});

test('exchange returns the magic login URL for a successful login state', function (): void {
    $verifier = pkceVerifier();
    $code = Str::random(64);
    Cache::put(STATE_PREFIX . $code, defaultLoginState(pkceChallenge($verifier), $this->user), now()->addMinutes(5));

    $response = $this->postJson(route('passkey-bridge.exchange'), [
        'code' => $code,
        'code_verifier' => $verifier,
    ]);

    $response->assertOk();
    expect($response->json('magic_login_url'))->not->toBeNull()
        ->and(Cache::get(STATE_PREFIX . $code))->toBeNull();
});

test('exchange returns ok for a successful register state', function (): void {
    $verifier = pkceVerifier();
    $code = Str::random(64);
    Cache::put(STATE_PREFIX . $code, defaultRegisterState(pkceChallenge($verifier), $this->user), now()->addMinutes(5));

    $response = $this->postJson(route('passkey-bridge.exchange'), [
        'code' => $code,
        'code_verifier' => $verifier,
    ]);

    $response->assertOk()->assertJson(['status' => 'ok']);
    expect(Cache::get(STATE_PREFIX . $code))->toBeNull();
});

test('exchange rejects a verifier that does not match the challenge', function (): void {
    $verifier = pkceVerifier();
    $code = Str::random(64);
    Cache::put(STATE_PREFIX . $code, defaultLoginState(pkceChallenge($verifier), $this->user), now()->addMinutes(5));

    $response = $this->postJson(route('passkey-bridge.exchange'), [
        'code' => $code,
        'code_verifier' => 'wrong-verifier-' . str_repeat('y', 30),
    ]);

    $response->assertStatus(400)->assertJson(['error' => 'invalid_verifier']);
    expect(Cache::get(STATE_PREFIX . $code))->toBeNull();
});

test('exchange rejects a code that has already been pulled', function (): void {
    $verifier = pkceVerifier();
    $code = Str::random(64);

    $response = $this->postJson(route('passkey-bridge.exchange'), [
        'code' => $code,
        'code_verifier' => $verifier,
    ]);

    $response->assertStatus(400)->assertJson(['error' => 'invalid_code']);
});

test('exchange rejects a code whose login has not been completed', function (): void {
    $verifier = pkceVerifier();
    $code = Str::random(64);
    Cache::put(STATE_PREFIX . $code, defaultLoginState(pkceChallenge($verifier)), now()->addMinutes(5));

    $response = $this->postJson(route('passkey-bridge.exchange'), [
        'code' => $code,
        'code_verifier' => $verifier,
    ]);

    $response->assertStatus(400)->assertJson(['error' => 'invalid_code']);
});

class FakeAuthOptionsAction extends GeneratePasskeyAuthenticationOptionsAction
{
    public function execute(): string
    {
        return json_encode(['challenge' => 'fake-challenge']);
    }
}

class FakeRegisterOptionsAction extends GeneratePasskeyRegisterOptionsAction
{
    public function execute(
        Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys $authenticatable,
        bool $asJson = true,
    ): string|Webauthn\PublicKeyCredentialCreationOptions {
        return json_encode(['challenge' => 'fake-challenge', 'user' => ['id' => (string) $authenticatable->getKey()]]);
    }
}

class FakeFindPasskeyAction extends FindPasskeyToAuthenticateAction
{
    public static ?User $user = null;

    public function execute(string $publicKeyCredentialJson, string $passkeyOptionsJson): ?Passkey
    {
        if (! self::$user) {
            return null;
        }

        return tap(new Passkey(), function (Passkey $passkey): void {
            $passkey->setAttribute('authenticatable_type', self::$user->getMorphClass());
            $passkey->setAttribute('authenticatable_id', self::$user->getKey());
            $passkey->setRelation('authenticatable', self::$user);
        });
    }
}

class FakeStorePasskeyAction extends StorePasskeyAction
{
    public static bool $called = false;

    public function execute(
        Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys $authenticatable,
        string $passkeyJson,
        string $passkeyOptionsJson,
        string $hostName,
        array $additionalProperties = [],
    ): Passkey {
        self::$called = true;

        return new Passkey();
    }
}

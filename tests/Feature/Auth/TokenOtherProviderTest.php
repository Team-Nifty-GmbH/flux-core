<?php

use FluxErp\Models\Token;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

function resolveBearer(string $guard, string $plainTextToken): mixed
{
    Auth::forgetGuards();
    request()->headers->set('Authorization', 'Bearer ' . $plainTextToken);

    return Auth::guard($guard)->user();
}

test('a user token shown to the api token guard is refused but kept', function (): void {
    $plainTextToken = $this->user->createToken('mcp-server')->plainTextToken;

    expect(resolveBearer('token', $plainTextToken))->toBeNull()
        ->and(PersonalAccessToken::findToken($plainTextToken))->not->toBeNull()
        ->and(resolveBearer('sanctum', $plainTextToken)?->getKey())->toBe($this->user->getKey());
});

test('an api token shown to the user guard is refused but kept', function (): void {
    $token = Token::query()->create(['name' => 'integration']);
    $plainTextToken = $token->createToken('integration', ['interface'])->plainTextToken;

    expect(resolveBearer('sanctum', $plainTextToken))->toBeNull()
        ->and(PersonalAccessToken::findToken($plainTextToken))->not->toBeNull()
        ->and(resolveBearer('token', $plainTextToken)?->getKey())->toBe($token->getKey());
});

test('an api token bound to another url is kept by the user guard', function (): void {
    $token = Token::query()->create(['name' => 'bound', 'url' => 'https://example.com/elsewhere']);
    $plainTextToken = $token->createToken('bound', ['interface'])->plainTextToken;

    expect(resolveBearer('sanctum', $plainTextToken))->toBeNull()
        ->and(PersonalAccessToken::findToken($plainTextToken))->not->toBeNull();
});

test('a token whose owner is gone is refused and deleted', function (): void {
    $accessToken = $this->user->createToken('orphaned');
    $accessToken->accessToken->forceFill(['tokenable_id' => 0])->save();

    expect(resolveBearer('sanctum', $accessToken->plainTextToken))->toBeNull()
        ->and(PersonalAccessToken::findToken($accessToken->plainTextToken))->toBeNull();
});

test('an expired user token is still deleted', function (): void {
    config(['sanctum.expiration' => 60]);
    Auth::forgetGuards();
    $accessToken = $this->user->createToken('expired');
    $accessToken->accessToken->forceFill(['last_used_at' => now()->subHours(2)])->save();

    expect(resolveBearer('sanctum', $accessToken->plainTextToken))->toBeNull()
        ->and(PersonalAccessToken::findToken($accessToken->plainTextToken))->toBeNull();
});

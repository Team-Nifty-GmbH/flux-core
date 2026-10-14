<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Token;
use Illuminate\Support\Facades\Auth;

function requestWithLimitedToken(object $test, string $plainTextToken): Illuminate\Testing\TestResponse
{
    Auth::forgetGuards();

    return $test->withToken($plainTextToken)
        ->getJson('/api/print-jobs');
}

test('an api token can be used exactly as often as its max uses allow', function (int $maxUses): void {
    $token = Token::query()->create(['name' => 'limited', 'max_uses' => $maxUses]);
    $token->givePermissionTo(Permission::findOrCreate('api.print-jobs.get', 'token'));
    $plainTextToken = $token->createToken('limited', ['interface'])->plainTextToken;

    foreach (range(1, $maxUses) as $ignored) {
        requestWithLimitedToken($this, $plainTextToken)->assertOk();
    }

    requestWithLimitedToken($this, $plainTextToken)->assertUnauthorized();

    expect($token->refresh()->uses)->toBe($maxUses);
})->with([1, 3]);

test('a token use is refused once another request claimed the last one', function (): void {
    $token = Token::query()->create(['name' => 'limited', 'max_uses' => 1]);
    $staleCopy = Token::query()->find($token->getKey());

    expect($token->use())->toBeTrue()
        ->and($staleCopy->isValid())->toBeTrue()
        ->and($staleCopy->use())->toBeFalse()
        ->and($token->refresh()->uses)->toBe(1);
});

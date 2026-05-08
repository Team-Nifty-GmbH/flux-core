<?php

namespace FluxErp\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction;
use Spatie\LaravelPasskeys\Http\Controllers\AuthenticateUsingPasskeyController as BaseAuthenticateUsingPasskeyController;
use Spatie\LaravelPasskeys\Http\Requests\AuthenticateUsingPasskeysRequest;
use Spatie\LaravelPasskeys\Support\Config;

class AuthenticateUsingPasskeyController extends BaseAuthenticateUsingPasskeyController
{
    public function __invoke(AuthenticateUsingPasskeysRequest $request)
    {
        // pull() instead of get(): atomic read-and-delete keeps the
        // single-use guarantee that the package's flash() relied on,
        // independent of Laravel's flash lifecycle.
        $passkeyOptions = Session::pull('passkey-authentication-options');

        if (blank($passkeyOptions)) {
            return $this->invalidPasskeyResponse();
        }

        $findAuthenticatableUsingPasskey = Config::getAction(
            'find_passkey',
            FindPasskeyToAuthenticateAction::class,
        );

        $passkey = $findAuthenticatableUsingPasskey->execute(
            $request->input('start_authentication_response'),
            $passkeyOptions,
        );

        if (! $passkey) {
            return $this->invalidPasskeyResponse();
        }

        /** @var Authenticatable $authenticatable */
        $authenticatable = $passkey->authenticatable;

        if (! $authenticatable) {
            return $this->invalidPasskeyResponse();
        }

        $this->logInAuthenticatable($authenticatable, $request->boolean('remember'));

        $this->firePasskeyEvent($passkey, $request);

        return $this->validPasskeyResponse($request);
    }

    protected function validPasskeyResponse(Request $request): RedirectResponse
    {
        if (Session::has('passkeys.redirect')) {
            return redirect(Session::pull('passkeys.redirect'));
        }

        return redirect(Session::get('url.intended', route('dashboard')));
    }
}

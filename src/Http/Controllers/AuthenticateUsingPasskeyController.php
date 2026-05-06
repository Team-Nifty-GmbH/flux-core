<?php

namespace FluxErp\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Spatie\LaravelPasskeys\Http\Controllers\AuthenticateUsingPasskeyController as BaseAuthenticateUsingPasskeyController;

class AuthenticateUsingPasskeyController extends BaseAuthenticateUsingPasskeyController
{
    protected function validPasskeyResponse(Request $request): RedirectResponse
    {
        if (Session::has('passkeys.redirect')) {
            return redirect(Session::pull('passkeys.redirect'));
        }

        return redirect(Session::get('url.intended', route('dashboard')));
    }
}

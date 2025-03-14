<?php

namespace FluxErp\Http\Controllers;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LoginLinkController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|View|Factory
    {
        $login = Cache::pull('login_token_' . $request->token);

        if (! $login) {
            return view('flux::login-link-failed');
        }

        try {
            Auth::guard($login['guard'])->login($login['user']);
        } catch (Exception) {
        }

        return Auth::guard($login['guard'])->check()
            ? redirect()->to($login['intended_url'])
            : view('flux::login-link-failed');
    }
}

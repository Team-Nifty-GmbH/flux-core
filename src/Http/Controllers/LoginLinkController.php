<?php

namespace FluxErp\Http\Controllers;

use App\Http\Controllers\Controller;
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
        $user = Cache::pull('login_token_' . $request->token);

        try {
            Auth::guard($user['guard'])->login($user['user']);
        } catch (\Exception $e) {
            return view('flux::login-link-failed');
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('flux::login-link-failed');
    }
}

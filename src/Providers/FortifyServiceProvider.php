<?php

namespace FluxErp\Providers;

use FluxErp\Actions\Fortify\CreateNewUser;
use FluxErp\Actions\Fortify\ResetUserPassword;
use FluxErp\Actions\Fortify\UpdateUserPassword;
use FluxErp\Actions\Fortify\UpdateUserProfileInformation;
use FluxErp\Models\Address;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (request()->isPortal()) {
            config(['fortify.domain' => config('flux.portal_domain')]);
            config(['fortify.guard' => 'address']);
            config(['fortify.email' => 'login_name']);
            config(['fortify.username' => 'login_name']);

            Fortify::authenticateUsing(function (Request $request) {
                $user = Address::query()
                    ->where('login_name', $request->login_name)
                    ->first();

                if ($user &&
                    Hash::check($request->password, $user->login_password)) {
                    return $user;
                }

                return false;
            });
        }

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::loginView(function () {
            if (request()->isPortal()) {
                return view('flux::fortify.portal-login');
            }

            return view('flux::fortify.login');
        });
    }
}

<?php

namespace FluxErp\Support\Auth;

use FluxErp\Traits\Makeable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Guard;
use Spatie\Permission\PermissionRegistrar;

class PermissionSet
{
    use Makeable;

    protected array $granted;

    protected bool $grantsEverything;

    protected array $known;

    public function __construct(?Authenticatable $user = null, ?string $guard = null)
    {
        $user ??= auth()->user();
        $guard ??= static::guardFor($user);

        $this->known = array_flip(
            app(PermissionRegistrar::class)
                ->getPermissions(['guard_name' => $guard])
                ->pluck('name')
                ->all()
        );

        $this->granted = is_null($user) || ! method_exists($user, 'getAllPermissions')
            ? []
            : array_flip(
                $user->getAllPermissions()
                    ->where('guard_name', $guard)
                    ->pluck('name')
                    ->all()
            );

        $this->grantsEverything = ! is_null($user)
            && Gate::forUser($user)->allows(static::blanketProbeAbility());
    }

    public static function blanketProbeAbility(): string
    {
        return 'flux.permission-set.blanket-probe';
    }

    public static function guardFor(?Authenticatable $user): string
    {
        if (is_null($user)) {
            return config('auth.defaults.guard');
        }

        return Guard::getNames($user)->first() ?? config('auth.defaults.guard');
    }

    public function allows(string $permission): bool
    {
        if (! array_key_exists($permission, $this->known)) {
            return true;
        }

        return $this->grantsEverything || array_key_exists($permission, $this->granted);
    }
}

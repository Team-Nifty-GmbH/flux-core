<?php

namespace FluxErp\Support\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

class PermissionSet
{
    public const BLANKET_PROBE = 'flux.permission-set.blanket-probe';

    protected array $granted;

    protected bool $grantsEverything;

    protected array $known;

    public function __construct(?Authenticatable $user, string $guard)
    {
        $this->known = array_flip(
            app(PermissionRegistrar::class)
                ->getPermissions(['guard_name' => $guard])
                ->pluck('name')
                ->all()
        );

        $this->granted = method_exists($user, 'getAllPermissions')
            ? array_flip($user->getAllPermissions()->pluck('name')->all())
            : [];

        $this->grantsEverything = ! is_null($user) && Gate::forUser($user)->allows(static::BLANKET_PROBE);
    }

    public static function make(?Authenticatable $user = null, ?string $guard = null): static
    {
        $user ??= auth()->user();

        return app(static::class, [
            'user' => $user,
            'guard' => $guard ?? config('auth.defaults.guard'),
        ]);
    }

    public function allows(string $permission): bool
    {
        if (! array_key_exists($permission, $this->known)) {
            return true;
        }

        return $this->grantsEverything || array_key_exists($permission, $this->granted);
    }
}

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

    protected ?array $granted = null;

    protected bool $grantsEverything;

    protected string $guard;

    protected ?array $known = null;

    protected ?Authenticatable $user;

    public function __construct(?Authenticatable $user = null, ?string $guard = null)
    {
        $this->user = $user ?? auth()->user();
        $this->guard = $guard ?? static::guardFor($this->user);
        $this->grantsEverything = ! is_null($this->user)
            && Gate::forUser($this->user)->allows(static::blanketProbeAbility());
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
        return $this->grantsEverything
            || ! array_key_exists($permission, $this->known())
            || array_key_exists($permission, $this->granted());
    }

    /**
     * Both sets cost a walk over every stored permission, and a gate that hands out
     * everything answers the question before either is consulted. Resolving them on
     * first use keeps that walk out of the request entirely for a super admin.
     */
    protected function granted(): array
    {
        return $this->granted ??= is_null($this->user) || ! method_exists($this->user, 'getAllPermissions')
            ? []
            : array_flip(
                $this->user->getAllPermissions()
                    ->where('guard_name', $this->guard)
                    ->pluck('name')
                    ->all()
            );
    }

    protected function known(): array
    {
        return $this->known ??= array_flip(
            app(PermissionRegistrar::class)
                ->getPermissions(['guard_name' => $this->guard])
                ->pluck('name')
                ->all()
        );
    }
}

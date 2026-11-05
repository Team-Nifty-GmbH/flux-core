<?php

namespace FluxErp\Traits\Action;

use FluxErp\Models\Scopes\UserTenantScope;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

trait ValidatesEmployeeUserLink
{
    protected function validateUserLink(int $tenantId, string $errorBag): void
    {
        if (! $this->getData('user_id')) {
            return;
        }

        $belongsToTenant = resolve_static(User::class, 'query')
            ->withoutGlobalScope(UserTenantScope::class)
            ->whereKey($this->getData('user_id'))
            ->whereHas('tenants', fn (Builder $query) => $query
                ->withoutGlobalScope(UserTenantScope::class)
                ->whereKey($tenantId)
            )
            ->exists();

        if ($belongsToTenant) {
            return;
        }

        throw ValidationException::withMessages([
            'user_id' => ['The selected user does not belong to the tenant of this employee.'],
        ])
            ->errorBag($errorBag);
    }
}

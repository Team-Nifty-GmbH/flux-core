<?php

namespace FluxErp\Traits\Model;

use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasTenants
{
    abstract public function tenants(): BelongsToMany;

    public function getTenant(): ?Model
    {
        return $this->tenants()
            ->orderBy('is_default', 'DESC')
            ->first()
            ?? resolve_static(Tenant::class, 'default');
    }

    public function getTenantId(): int|string|null
    {
        return $this->tenants()
            ->orderBy('is_default', 'DESC')
            ->value('id')
            ?? resolve_static(Tenant::class, 'default')
                ?->getKey();
    }

    public function getTenants(array $columns = ['*']): Collection
    {
        $tenants = $this->tenants()->get($columns);

        if ($tenants->isEmpty()) {
            $tenants = resolve_static(Tenant::class, 'query')
                ->get($columns);
        }

        return $tenants;
    }

    #[Scope]
    protected function whereHasTenant(Builder $query, array|int|string|Model|Collection|null $tenant): void
    {
        if (is_null($tenant)) {
            $query->whereDoesntHave('tenants');

            return;
        }

        $query->where(
            fn (Builder $query) => $query
                ->whereHas('tenants', fn (Builder $query) => $query->whereKey($tenant))
                ->orWhereDoesntHave('tenants')
        );
    }
}

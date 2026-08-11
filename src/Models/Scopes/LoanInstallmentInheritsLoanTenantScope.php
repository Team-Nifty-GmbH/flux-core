<?php

namespace FluxErp\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LoanInstallmentInheritsLoanTenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('loan');
    }
}

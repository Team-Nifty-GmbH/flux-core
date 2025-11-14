<?php

namespace FluxErp\Actions\Tenant;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tenant;
use FluxErp\Rulesets\Tenant\UpdateTenantRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateTenant extends FluxAction
{
    public static function models(): array
    {
        return [Tenant::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateTenantRuleset::class;
    }

    public function performAction(): Model
    {
        $bankConnections = Arr::pull($this->data, 'bank_connections');
        $tenant = resolve_static(Tenant::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $tenant->fill($this->data);
        $tenant->save();

        if (! is_null($bankConnections)) {
            $tenant->bankConnections()->sync($bankConnections);
        }

        return $tenant->withoutRelations()->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules['tenant_code'] .= ',' . ($this->data['id'] ?? 0);

        if (($this->data['is_default'] ?? false)
            && ! resolve_static(Tenant::class, 'query')
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }
    }
}

<?php

namespace FluxErp\Actions\Tenant;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tenant;
use FluxErp\Rulesets\Tenant\CreateTenantRuleset;
use Illuminate\Support\Arr;

class CreateTenant extends FluxAction
{
    public static function models(): array
    {
        return [Tenant::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateTenantRuleset::class;
    }

    public function performAction(): Tenant
    {
        $bankConnections = Arr::pull($this->data, 'bank_connections');

        /** @var Tenant $tenant */
        $tenant = app(Tenant::class, ['attributes' => $this->data]);
        $tenant->save();

        if ($bankConnections) {
            $tenant->bankConnections()->sync($bankConnections);
        }

        return $tenant->refresh();
    }
}

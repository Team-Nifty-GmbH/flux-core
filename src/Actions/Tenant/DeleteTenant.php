<?php

namespace FluxErp\Actions\Tenant;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tenant;
use FluxErp\Rulesets\Tenant\DeleteTenantRuleset;

class DeleteTenant extends FluxAction
{
    public static function models(): array
    {
        return [Tenant::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteTenantRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Tenant::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}

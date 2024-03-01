<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Rulesets\Plugin\UpdatePluginRuleset;

class Update extends BasePluginAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdatePluginRuleset::class, 'getRules');
    }

    public function performAction(): mixed
    {
        /** @var \FluxErp\Helpers\Composer $composer */
        $composer = app('composer');
        $composer->updatePackages($this->data['packages']);

        if ($this->data['migrate'] ?? true) {
            $this::migrate($this->data['packages']);
        }

        return app('composer')->updatePackages($this->data[0] ?? null);
    }
}

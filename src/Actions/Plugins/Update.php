<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Http\Requests\PluginUpdateRequest;

class Update extends BasePluginAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new PluginUpdateRequest())->rules();
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

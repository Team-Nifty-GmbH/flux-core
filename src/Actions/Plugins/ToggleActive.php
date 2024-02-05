<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Http\Requests\PluginToggleActiveRequest;

class ToggleActive extends BasePluginAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new PluginToggleActiveRequest())->rules();
    }

    public function performAction(): true
    {
        /** @var \FluxErp\Helpers\Composer $composer */
        $composer = app('composer');
        $composer->modify(function ($composer) {
            $discover = $composer['extra']['laravel']['dont-discover'] ?? [];
            foreach ($this->data['packages'] as $package) {
                if (in_array($package, $discover)) {
                    $discover = array_diff($discover, [$package]);
                } else {
                    $discover[] = $package;
                }
            }

            $composer['extra']['laravel']['dont-discover'] = $discover;

            return $composer;
        });

        $run = $composer->dumpAutoloads();

        if ($run) {
            throw new \RuntimeException('Could not dump autoloads.');
        }

        return true;
    }
}

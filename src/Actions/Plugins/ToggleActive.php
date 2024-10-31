<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Rulesets\Plugin\TogglePluginRuleset;

class ToggleActive extends BasePluginAction
{
    protected function getRulesets(): string|array
    {
        return TogglePluginRuleset::class;
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

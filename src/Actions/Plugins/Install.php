<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Rulesets\Plugin\InstallPluginRuleset;

class Install extends BasePluginAction
{
    public static function getRulesets(): string|array
    {
        return InstallPluginRuleset::class;
    }

    public function performAction(): true
    {
        /** @var \FluxErp\Helpers\Composer $composer */
        $composer = app('composer');
        $output = null;

        $command = array_merge(
            $this->data['packages'],
            ['--no-progress', '--no-interaction', '--no-ansi'],
            $this->data['options'] ?? []
        );
        $run = $composer->requirePackages($command, false, function ($type, $buffer) use (&$output) {
            $output .= $buffer;
        });

        if (! $run) {
            throw new \RuntimeException($output);
        }

        if ($this->data['migrate'] ?? true) {
            $this::migrate($this->data['packages']);
        }

        return $run;
    }
}

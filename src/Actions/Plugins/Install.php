<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Rulesets\Plugin\InstallPluginRuleset;

class Install extends BasePluginAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(InstallPluginRuleset::class, 'getRules');
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

<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Http\Requests\PluginInstallRequest;

class Install extends BasePluginAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new PluginInstallRequest())->rules();
    }

    public function performAction(): true
    {
        /** @var \FluxErp\Helpers\Composer $composer */
        $composer = app('composer');
        $output = null;

        $command = array_merge(
            $this->data['packages'],
            ['--no-progress', '--no-interaction', '--no-suggest', '--no-ansi'],
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

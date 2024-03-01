<?php

namespace FluxErp\Actions\Plugins;

use FluxErp\Rulesets\Plugin\UninstallPluginRuleset;
use Illuminate\Validation\ValidationException;

class Uninstall extends BasePluginAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UninstallPluginRuleset::class, 'getRules');
    }

    public function performAction(): bool
    {
        /** @var \FluxErp\Helpers\Composer $composer */
        $composer = app('composer');

        if ($this->data['rollback'] ?? false) {
            $this::migrate($this->data['packages'], true);
        }

        $output = '';
        $run = $composer->removePackages(
            $this->data['packages'],
            false,
            function ($type, $buffer) use (&$output) {
                $output .= $buffer;
            }
        );

        if (! $run) {
            throw new \RuntimeException($output);
        }

        return $run;
    }

    protected function validateData(): void
    {
        parent::validateData();

        /** @var \FluxErp\Helpers\Composer $composer */
        $composer = app('composer');
        $installedPackages = array_keys($composer->installed(true)['installed']);

        $errors = [];
        foreach ($this->data['packages'] as $key => $package) {
            if (in_array($package, ['laravel/framework', 'team-nifty-gmbh/flux-erp'])) {
                $errors += [
                    'packages.' . $key => ['Unable to uninstall \'' . $package . '\'.'],
                ];
            } elseif (! in_array($package, $installedPackages)) {
                $errors += [
                    'packages.' . $key => ['Plugin \'' . $package . '\' not installed.'],
                ];
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('uninstallPlugins');
        }
    }
}

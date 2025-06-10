<?php

namespace FluxErp\Actions\Console;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Rulesets\Console\RunCommandRuleset;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class RunCommand extends DispatchableFluxAction
{
    public static function models(): array
    {
        return [];
    }

    protected function getRulesets(): string|array
    {
        return RunCommandRuleset::class;
    }

    public function performAction(): array
    {
        $result = Artisan::call(
            $this->getData('command'),
            $this->getData('arguments', []),
            $output = new BufferedOutput()
        );

        return [
            'result' => $result,
            'output' => $output->fetch(),
        ];
    }
}

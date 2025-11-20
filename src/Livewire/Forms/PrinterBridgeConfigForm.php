<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Printer\GeneratePrinterBridgeConfig;

class PrinterBridgeConfigForm extends FluxForm
{
    public array $bridgeConfig = [];

    public string $instanceName = 'default-instance';

    public int $printerCheckInterval = 5;

    public int $jobCheckInterval = 2;

    public int $apiPort = 8080;

    public bool $reverbDisabled = false;

    public bool $forceRegenerate = false;

    public function toActionData(): array
    {
        return [
            'instance_name' => $this->instanceName,
            'printer_check_interval' => $this->printerCheckInterval,
            'job_check_interval' => $this->jobCheckInterval,
            'api_port' => $this->apiPort,
            'reverb_disabled' => $this->reverbDisabled,
            'force_regenerate' => $this->forceRegenerate,
        ];
    }

    public function generate(): array
    {
        $action = $this->makeAction('generate')
            ->checkPermission()
            ->validate();

        $this->bridgeConfig = $action->execute();

        return $this->bridgeConfig;
    }

    protected function getActions(): array
    {
        return [
            'generate' => GeneratePrinterBridgeConfig::class,
        ];
    }
}

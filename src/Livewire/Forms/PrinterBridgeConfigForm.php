<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Printer\GeneratePrinterBridgeConfig;
use FluxErp\Support\Livewire\Attributes\ExcludeFromActionData;

class PrinterBridgeConfigForm extends FluxForm
{
    #[ExcludeFromActionData]
    public array $bridge_config = [];

    public string $instance_name = 'default-instance';

    public int $printer_check_interval = 5;

    public int $job_check_interval = 2;

    public int $api_port = 8080;

    public bool $reverb_disabled = false;

    public bool $force_regenerate = false;

    public function create(): void
    {
        $action = $this->makeAction('create')
            ->checkPermission()
            ->validate();

        $this->bridge_config = $action->execute();
    }

    protected function getActions(): array
    {
        return [
            'create' => GeneratePrinterBridgeConfig::class,
        ];
    }
}

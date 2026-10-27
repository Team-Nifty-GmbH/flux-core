<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Printer\GeneratePrinterBridgeConfig;
use FluxErp\Support\Livewire\Attributes\ExcludeFromActionData;

class PrinterBridgeConfigForm extends FluxForm
{
    #[ExcludeFromActionData]
    public array $bridge_config = [];

    public string $instance_name;

    public int $printer_check_interval;

    public int $job_check_interval;

    public int $api_port;

    public bool $reverb_disabled;

    public bool $mercure_enabled;

    public bool $force_regenerate;

    public function create(): void
    {
        parent::create();

        $this->bridge_config = $this->actionResult;
    }

    protected function getActions(): array
    {
        return [
            'create' => GeneratePrinterBridgeConfig::class,
        ];
    }
}

<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Printer\GeneratePrinterBridgeConfig;
use FluxErp\Support\Livewire\Attributes\ExcludeFromActionData;

class PrinterBridgeConfigForm extends FluxForm
{
    #[ExcludeFromActionData]
    public array $bridge_config = [];

    public ?string $instance_name = null;

    public ?int $printer_check_interval = null;

    public ?int $job_check_interval = null;

    public ?int $api_port = null;

    public ?bool $reverb_disabled = null;

    public ?bool $mercure_enabled = null;

    public ?bool $force_regenerate = null;

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

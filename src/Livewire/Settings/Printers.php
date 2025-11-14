<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Printer\GeneratePrinterBridgeConfig;
use FluxErp\Livewire\DataTables\PrinterList;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Printers extends PrinterList
{
    public array $bridgeConfig = [];

    public string $instanceName = 'default-instance';

    public int $printerCheckInterval = 5;

    public int $jobCheckInterval = 2;

    public int $apiPort = 8080;

    public bool $reverbDisabled = false;

    protected ?string $includeBefore = 'flux::livewire.settings.printers';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Generate Bridge Config'))
                ->color('primary')
                ->icon('cog')
                ->wireClick('openBridgeConfigModal'),
        ];
    }

    public function openBridgeConfigModal(): void
    {
        $this->js(<<<'JS'
            $modalOpen('printer-bridge-config-modal');
        JS);
    }

    public function generateBridgeConfig(): bool
    {
        try {
            $this->bridgeConfig = GeneratePrinterBridgeConfig::make([
                'instance_name' => $this->instanceName,
                'printer_check_interval' => $this->printerCheckInterval,
                'job_check_interval' => $this->jobCheckInterval,
                'api_port' => $this->apiPort,
                'reverb_disabled' => $this->reverbDisabled,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }

    public function copyToClipboard(): void
    {
        $this->dispatch('clipboard-copy', json_encode($this->bridgeConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->toast()
            ->success(__('Copied!'), __('Configuration copied to clipboard'))
            ->send();
    }
}

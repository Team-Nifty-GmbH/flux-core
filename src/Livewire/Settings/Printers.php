<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Printer\GeneratePrinterBridgeConfig;
use FluxErp\Livewire\DataTables\PrinterList;
use FluxErp\Models\Token;
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

    public bool $forceRegenerate = false;

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

    public function generateBridgeConfig(): void
    {
        $existingToken = Token::where('name', $this->instanceName)->first();

        if ($existingToken && ! $this->forceRegenerate) {
            $this->dispatch('confirm-token-regeneration');

            return;
        }

        try {
            $this->bridgeConfig = GeneratePrinterBridgeConfig::make([
                'instance_name' => $this->instanceName,
                'printer_check_interval' => $this->printerCheckInterval,
                'job_check_interval' => $this->jobCheckInterval,
                'api_port' => $this->apiPort,
                'reverb_disabled' => $this->reverbDisabled,
                'force_regenerate' => $this->forceRegenerate,
            ])
                ->checkPermission()
                ->validate()
                ->execute();

            $this->toast()
                ->success(__('Success'), __('Configuration generated successfully'))
                ->send();

            $this->dispatch('config-generated');

            $this->forceRegenerate = false;
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            $this->toast()
                ->error(__('Error'), __('Failed to generate configuration'))
                ->send();
        }
    }

    public function confirmRegeneration(): void
    {
        $this->forceRegenerate = true;
        $this->generateBridgeConfig();
    }

    public function copyToClipboard(): void
    {
        $this->toast()
            ->success(__('Copied!'), __('Configuration copied to clipboard'))
            ->send();
    }
}

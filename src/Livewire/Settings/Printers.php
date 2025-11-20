<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\PrinterList;
use FluxErp\Livewire\Forms\PrinterBridgeConfigForm;
use FluxErp\Models\Token;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Printers extends PrinterList
{
    public PrinterBridgeConfigForm $configForm;

    protected ?string $includeBefore = 'flux::livewire.settings.printers';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Generate Bridge Config'))
                ->color('primary')
                ->icon('cog')
                ->xOnClick("\$modalOpen('printer-bridge-config-modal')"),
        ];
    }

    #[Renderless]
    public function generateBridgeConfig(): void
    {
        $existingToken = resolve_static(Token::class, 'query')
            ->where('name', $this->configForm->instanceName)
            ->first();

        if ($existingToken && ! $this->configForm->forceRegenerate) {
            $title = __('Token Already Exists');
            $description = __('A token with this instance name already exists. Regenerating will invalidate the old token. Do you want to continue?');
            $confirmText = __('Yes, Regenerate Token');
            $cancelText = __('Cancel');

            $this->js(<<<JS
                \$interaction('dialog')
                    .wireable(\$wire.__instance.id)
                    .warning(
                        '{$title}',
                        '{$description}'
                    )
                    .confirm('{$confirmText}', 'confirmRegeneration', '{$cancelText}')
                    .send();
            JS);

            return;
        }

        try {
            $this->configForm->generate();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->toast()
            ->success(__('Success'), __('Configuration generated successfully'))
            ->send();

        $this->dispatch('config-generated');

        $this->configForm->forceRegenerate = false;
    }

    #[Renderless]
    public function confirmRegeneration(): void
    {
        $this->configForm->forceRegenerate = true;
        $this->generateBridgeConfig();
    }
}

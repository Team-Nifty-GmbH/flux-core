<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Printer\UpdatePrinter;
use FluxErp\Livewire\DataTables\PrinterList;
use FluxErp\Livewire\Forms\PrinterBridgeConfigForm;
use FluxErp\Livewire\Forms\PrinterForm;
use FluxErp\Models\Printer;
use FluxErp\Models\Token;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Printers extends PrinterList
{
    public PrinterBridgeConfigForm $configForm;

    public PrinterForm $printerForm;

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

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdatePrinter::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
        ];
    }

    public function edit(Printer $printer): void
    {
        $this->printerForm->reset();
        $this->printerForm->fill($printer);

        $this->js(<<<'JS'
            $modalOpen('edit-printer-modal');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->printerForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function generateBridgeConfig(): void
    {
        $existingToken = resolve_static(Token::class, 'query')
            ->where('name', $this->configForm->instance_name)
            ->first();

        if ($existingToken && ! $this->configForm->force_regenerate) {
            $this->dialog()
                ->warning(
                    __('Token Already Exists'),
                    __('A token with this instance name already exists. Regenerating will invalidate the old token. Do you want to continue?')
                )
                ->confirm(__('Yes, Regenerate Token'), 'confirmRegeneration')
                ->cancel(__('Cancel'))
                ->send();

            return;
        }

        try {
            $this->configForm->create();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->toast()
            ->success(__('Success'), __('Configuration generated successfully'))
            ->send();

        $this->dispatch('config-generated');

        $this->configForm->force_regenerate = false;
    }

    #[Renderless]
    public function confirmRegeneration(): void
    {
        $this->configForm->force_regenerate = true;
        $this->generateBridgeConfig();
    }
}

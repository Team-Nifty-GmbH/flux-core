<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Printer\DeletePrinter;
use FluxErp\Actions\Printer\UpdatePrinter;
use FluxErp\Livewire\DataTables\PrinterList;
use FluxErp\Livewire\Forms\PrinterBridgeConfigForm;
use FluxErp\Livewire\Forms\PrinterForm;
use FluxErp\Models\Printer;
use FluxErp\Models\Token;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Printers extends PrinterList
{
    use Actions;

    public PrinterBridgeConfigForm $configForm;

    public PrinterForm $printerForm;

    public string $deleteSpoolerName = '';

    protected ?string $includeBefore = 'flux::livewire.settings.printers';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Generate Bridge Config'))
                ->color('primary')
                ->icon('cog')
                ->xOnClick("\$modalOpen('printer-bridge-config-modal')"),
            DataTableButton::make()
                ->text(__('Delete Spooler'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeletePrinter::class, 'canPerformAction', [false]))
                ->xOnClick("\$modalOpen('delete-spooler-modal')"),
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

        $this->modalOpen('edit-printer-modal');
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

    public function deleteSpooler(): bool
    {
        if (empty($this->deleteSpoolerName)) {
            $this->toast()
                ->error(__('Error'), __('Please select a spooler to delete'))
                ->send();

            return false;
        }

        $printers = resolve_static(Printer::class, 'query')
            ->where('spooler_name', $this->deleteSpoolerName)
            ->get();

        foreach ($printers as $printer) {
            try {
                DeletePrinter::make(['id' => $printer->id])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                return false;
            }
        }

        resolve_static(Token::class, 'query')
            ->where('name', $this->deleteSpoolerName)
            ->whereNull('expires_at')
            ->update(['expires_at' => now()]);

        $this->toast()
            ->success(__('Success'), __('Spooler and associated printers deleted successfully'))
            ->send();

        $this->deleteSpoolerName = '';
        $this->loadData();

        return true;
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'spoolerNames' => resolve_static(Printer::class, 'query')
                    ->distinct()
                    ->pluck('spooler_name')
                    ->filter()
                    ->map(fn ($name) => ['label' => $name, 'value' => $name])
                    ->values()
                    ->toArray(),
            ]
        );
    }
}

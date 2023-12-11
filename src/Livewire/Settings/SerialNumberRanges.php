<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\SerialNumberRange\DeleteSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\UpdateSerialNumberRange;
use FluxErp\Actions\VatRate\CreateVatRate;
use FluxErp\Livewire\DataTables\SerialNumberRangeList;
use FluxErp\Livewire\Forms\SerialNumberRangeForm;
use FluxErp\Models\Client;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Traits\HasSerialNumberRange;
use Illuminate\Validation\ValidationException;
use Spatie\ModelInfo\ModelInfo;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class SerialNumberRanges extends SerialNumberRangeList
{
    use Actions;

    public string $view = 'flux::livewire.settings.serial-number-ranges';

    public SerialNumberRangeForm $serialNumberRange;

    public function mount(): void
    {
        parent::mount();

        $this->headline = __('Serial Number Ranges');
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'models' => model_info_all()
                    ->filter(fn (ModelInfo $modelInfo) => $modelInfo->traits->contains(HasSerialNumberRange::class))
                    ->pluck('class')
                    ->toArray(),
                'clients' => Client::query()
                    ->select('id', 'name')
                    ->get()
                    ->toArray(),
            ]
        );
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->when(CreateVatRate::canPerformAction(false))
                ->attributes(
                    ['wire:click' => 'edit']
                ),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(UpdateSerialNumberRange::canPerformAction(false))
                ->attributes(
                    ['wire:click' => 'edit(record.id)']
                ),
        ];
    }

    public function edit(SerialNumberRange $serialNumberRange): void
    {
        $this->serialNumberRange->reset();
        $this->serialNumberRange->fill($serialNumberRange);

        $this->js(<<<'JS'
            $openModal('edit-serial-number-range');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->serialNumberRange->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(): bool
    {
        try {
            DeleteSerialNumberRange::make($this->serialNumberRange->toArray())
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}

<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\SerialNumberRange\CreateSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\DeleteSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\UpdateSerialNumberRange;
use FluxErp\Livewire\DataTables\SerialNumberRangeList;
use FluxErp\Livewire\Forms\SerialNumberRangeForm;
use FluxErp\Models\Client;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\ModelInfo\ModelInfo;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class SerialNumberRanges extends SerialNumberRangeList
{
    use Actions;

    public ?string $includeBefore = 'flux::livewire.settings.serial-number-ranges';

    public SerialNumberRangeForm $serialNumberRange;

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'models' => model_info_all()
                    ->unique('morphClass')
                    ->filter(fn (ModelInfo $modelInfo) => $modelInfo->traits->contains(HasSerialNumberRange::class))
                    ->map(fn ($modelInfo) => [
                        'label' => __(Str::headline($modelInfo->morphClass)),
                        'value' => $modelInfo->morphClass,
                    ])
                    ->toArray(),
                'clients' => resolve_static(Client::class, 'query')
                    ->select('id', 'name')
                    ->get()
                    ->toArray(),
            ]
        );
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(CreateSerialNumberRange::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdateSerialNumberRange::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
        ];
    }

    public function edit(SerialNumberRange $serialNumberRange): void
    {
        $this->serialNumberRange->reset();
        $this->serialNumberRange->fill($serialNumberRange);

        $this->js(<<<'JS'
            $modalOpen('edit-serial-number-range');
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

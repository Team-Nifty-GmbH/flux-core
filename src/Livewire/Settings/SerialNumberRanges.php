<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\SerialNumberRange\CreateSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\DeleteSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\UpdateSerialNumberRange;
use FluxErp\Livewire\DataTables\SerialNumberRangeList;
use FluxErp\Livewire\Forms\SerialNumberRangeForm;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Models\Tenant;
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
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteSerialNumberRange::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Serial Number Range')]),
                ]),
        ];
    }

    public function delete(SerialNumberRange $serialNumberRange): bool
    {
        $this->serialNumberRange->reset();
        $this->serialNumberRange->fill($serialNumberRange);

        try {
            $this->serialNumberRange->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function edit(SerialNumberRange $serialNumberRange): void
    {
        $this->serialNumberRange->reset();
        $this->serialNumberRange->fill($serialNumberRange);

        $this->js(<<<'JS'
            $modalOpen('edit-serial-number-range-modal');
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
                'tenants' => resolve_static(Tenant::class, 'query')
                    ->select('id', 'name')
                    ->get()
                    ->toArray(),
            ]
        );
    }
}

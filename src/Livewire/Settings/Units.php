<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Unit\CreateUnit;
use FluxErp\Actions\Unit\DeleteUnit;
use FluxErp\Actions\Unit\UpdateUnit;
use FluxErp\Livewire\DataTables\UnitList;
use FluxErp\Livewire\Forms\UnitForm;
use FluxErp\Models\Unit;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class Units extends UnitList
{
    use Actions;

    public UnitForm $unit;

    protected ?string $includeBefore = 'flux::livewire.settings.units';

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->when(resolve_static(CreateUnit::class, 'canPerformAction', [false]))
                ->wireClick('edit'),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->when(resolve_static(UpdateUnit::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->label(__('Delete'))
                ->color('negative')
                ->icon('trash')
                ->when(resolve_static(DeleteUnit::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Unit')]),
                ]),
        ];
    }

    public function edit(Unit $unit): void
    {
        $this->unit->reset();
        $this->unit->fill($unit);

        $this->js(<<<'JS'
            $openModal('edit-unit');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->unit->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(Unit $unit): bool
    {
        $this->unit->reset();
        $this->unit->fill($unit);

        try {
            $this->unit->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}

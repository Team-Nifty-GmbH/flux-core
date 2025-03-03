<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Industry\CreateIndustry;
use FluxErp\Actions\Industry\DeleteIndustry;
use FluxErp\Actions\Industry\UpdateIndustry;
use FluxErp\Livewire\DataTables\IndustryList;
use FluxErp\Livewire\Forms\IndustryForm;
use FluxErp\Models\Industry;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Industries extends IndustryList
{
    public IndustryForm $industryForm;

    protected ?string $includeBefore = 'flux::livewire.settings.industries';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->when(resolve_static(CreateIndustry::class, 'canPerformAction', [false]))
                ->wireClick('edit'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdateIndustry::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteIndustry::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Industry')]),
                ]),
        ];
    }

    public function edit(Industry $industry): void
    {
        $this->industryForm->reset();
        $this->industryForm->fill($industry);

        $this->js(<<<'JS'
            $modalOpen('edit-industry-modal');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->industryForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(Industry $industry): bool
    {
        $this->industryForm->reset();
        $this->industryForm->fill($industry);

        try {
            $this->industryForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}

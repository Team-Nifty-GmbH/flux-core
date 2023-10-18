<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\FormBuilderForm;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class FormBuilderFormList extends DataTable
{
    protected string $model = FormBuilderForm::class;

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->attributes([
                    'wire:click' => '$parent.editItem(record.id)',
                ]),
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->attributes([
                    'wire:click' => '$parent.deleteItem(record.id)',
                    'wire:loading.attr' => 'disabled',
                ]),
        ];
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->icon('plus')
                ->color('primary')
                ->attributes([
                    'wire:click' => '$parent.editItem(null)',
                ]),
        ];
    }

    public function deleteItem(FormBuilderForm $form): void
    {
//        $this->skipRender();
//
//        try {
//            DeleteFormBuilderForm::make($form->toArray())
//                ->checkPermission()
//                ->validate()
//                ->execute();
//        } catch (\Exception $e) {
//            exception_to_notifications($e, $this);
//
//            return;
//        }
//
//        $this->loadData();
    }
}

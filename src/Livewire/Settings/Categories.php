<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Category\CreateCategory;
use FluxErp\Actions\Category\DeleteCategory;
use FluxErp\Actions\Category\UpdateCategory;
use FluxErp\Livewire\DataTables\CategoryList;
use FluxErp\Livewire\Forms\CategoryForm;
use FluxErp\Models\Category;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\DataTable\AllowRecordMerging;
use FluxErp\Traits\Livewire\DataTable\SupportsLocalization;
use FluxErp\Traits\Model\Categorizable;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Categories extends CategoryList
{
    use Actions, AllowRecordMerging, SupportsLocalization;

    public CategoryForm $category;

    public bool $isSelectable = true;

    protected ?string $includeBefore = 'flux::livewire.settings.categories';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
                ->icon('plus')
                ->when(resolve_static(CreateCategory::class, 'canPerformAction', [false]))
                ->wireClick('edit()'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->color('indigo')
                ->icon('pencil')
                ->when(resolve_static(UpdateCategory::class, 'canPerformAction', [false]))
                ->wireClick('edit(record.id)'),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteCategory::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Category')]),
                ]),
        ];
    }

    #[Renderless]
    public function delete(Category $category): bool
    {
        $this->category->reset();
        $this->category->fill($category);

        try {
            $this->category->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function edit(Category $category): void
    {
        $this->category->reset();
        $this->category->fill($category);

        $this->js(<<<'JS'
            $modalOpen('edit-category-modal');
        JS);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->category->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'models' => get_models_with_trait(Categorizable::class),
        ]);
    }
}

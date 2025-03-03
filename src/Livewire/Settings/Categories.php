<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Category\CreateCategory;
use FluxErp\Actions\Category\DeleteCategory;
use FluxErp\Actions\Category\UpdateCategory;
use FluxErp\Livewire\DataTables\CategoryList;
use FluxErp\Livewire\Forms\CategoryForm;
use FluxErp\Models\Category;
use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Categories extends CategoryList
{
    use Actions;

    protected ?string $includeBefore = 'flux::livewire.settings.categories';

    public CategoryForm $category;

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'models' => model_info_all()
                ->filter(fn ($modelInfo) => in_array(
                    Categorizable::class,
                    class_uses_recursive($modelInfo->class)
                ))
                ->unique('morphClass')
                ->map(fn ($modelInfo) => [
                    'label' => __(Str::headline($modelInfo->morphClass)),
                    'value' => $modelInfo->morphClass,
                ])
                ->toArray(),
        ]);
    }

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
    public function edit(Category $category): void
    {
        $this->category->reset();
        $this->category->fill($category);

        $this->js(<<<'JS'
            $modalOpen('edit-category');
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
}

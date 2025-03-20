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
use FluxErp\Traits\Livewire\DataTable\SupportsLocalization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Categories extends CategoryList
{
    use Actions, SupportsLocalization;

    public CategoryForm $category;

    public ?int $languageId;

    public array $languages = [];

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
        $this->category->fill($category->localize(Session::get('selectedLanguageId')));

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

    protected function getResultFromQuery(Builder $query): array
    {
        $tree = to_flat_tree(
            $query->get()
                ->localize()
                ->toArray()
        );

        $returnKeys = array_merge($this->getReturnKeys(), ['depth']);

        foreach ($tree as &$item) {
            $item = Arr::only(Arr::dot($item), $returnKeys);
            $item['indentation'] = '';

            if ($item['depth'] > 0) {
                $indent = $item['depth'] * 20;
                $item['indentation'] = <<<HTML
                    <div class="text-right indent-icon" style="width:{$indent}px;">
                    </div>
                    HTML;
            }
        }

        return $tree;
    }

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
}

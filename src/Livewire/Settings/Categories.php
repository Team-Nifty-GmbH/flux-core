<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\CategoryList;
use FluxErp\Livewire\Forms\CategoryForm;
use FluxErp\Traits\Categorizable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class Categories extends CategoryList
{
    use Actions;

    protected string $view = 'flux::livewire.settings.categories';

    public CategoryForm $category;

    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'models' => model_info_all()
                ->unique('morphClass')
                ->filter(fn ($modelInfo) => in_array(Categorizable::class, $modelInfo->traits->toArray()))
                ->map(fn ($modelInfo) => [
                    'label' => __(Str::headline($modelInfo->morphClass)),
                    'value' => $modelInfo->morphClass,
                ])
                ->toArray(),
        ]);
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'x-on:click' => 'create()',
                ]),
        ];
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->color('primary')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => 'edit(record)',
                ]),
        ];
    }

    public function edit(?array $record = null): void
    {
        if ($record) {
            $this->category->fill(app($this->model)->query()->whereKey($record['id'])->firstOrFail());
        } else {
            $this->category->reset();
        }

        $this->forceRender();
    }

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

    public function delete(): bool
    {
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

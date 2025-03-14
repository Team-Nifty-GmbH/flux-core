<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Support\Livewire\Attributes\DataTableForm;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

trait DataTableHasFormEdit
{
    public function bootSupportsFluxForms(): void
    {
        if (! $this instanceof DataTable) {
            throw new InvalidArgumentException('This trait can only be used in a DataTable');
        }
    }

    #[Renderless]
    public function delete(string|int $id): bool
    {
        $model = resolve_static($this->model, 'query')->whereKey($id)->firstOrFail();

        $this->{$this->formAttributeName()}->reset();
        $this->{$this->formAttributeName()}->fill($model);

        try {
            $this->{$this->formAttributeName()}->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function deleteSelected(): void
    {
        if (! $this->supportBatchDelete()) {
            throw new InvalidArgumentException('Batch delete is not supported');
        }

        $this->{$this->formAttributeName()}->reset();

        foreach ($this->getSelectedModelsQuery()->pluck('id') as $id) {
            try {
                $this->{$this->formAttributeName()}->id = $id;
                $this->{$this->formAttributeName()}->delete();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                break;
            }
        }

        $this->loadData();

        $this->reset('selected');
    }

    #[Renderless]
    public function edit(string|int|null $id = null): void
    {
        $this->{$this->formAttributeName()}->reset();

        if ($id) {
            $model = resolve_static($this->model, 'query')->whereKey($id)->firstOrFail();
            $this->{$this->formAttributeName()}->fill($model);
        }

        $modalName = $this->modalName();
        $this->js(<<<JS
            \$modalOpen('$modalName');
        JS);
    }

    abstract public function loadData(): void;

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->{$this->formAttributeName()}->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    protected function formAttributeName(): string
    {
        return $this->getFormAttribute()->getName();
    }

    protected function getFormAttribute(): DataTableForm
    {
        return $this->getAttributes()->first(fn ($attribute) => $attribute instanceof DataTableForm);
    }

    protected function getRowActionsSupportsFluxForms(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when($this->{$this->formAttributeName()}->canAction('update'))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when($this->{$this->formAttributeName()}->canAction('delete'))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __(
                        'wire:confirm.delete',
                        ['model' => __(Str::headline(morph_alias($this->getModel())))]
                    ),
                ]),
        ];
    }

    protected function getSelectedActionsSupportsFluxForms(): array
    {
        if (! $this->supportBatchDelete()) {
            return [];
        }

        return [
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->when($this->{$this->formAttributeName()}->canAction('delete'))
                ->attributes([
                    'wire:click' => 'deleteSelected',
                    'wire:flux-confirm.type.error' => __(
                        'wire:confirm.delete',
                        ['model' => __(Str::headline(morph_alias($this->getModel())))]
                    ),
                ]),
        ];
    }

    protected function getTableActionsSupportsFluxForms(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when($this->{$this->formAttributeName()}->canAction('create'))
                ->attributes([
                    'wire:click' => 'edit',
                ]),
        ];
    }

    protected function modalName(): string
    {
        return method_exists($this->getPropertyValue($this->getFormAttribute()->getName()), 'modalName')
            ? $this->getPropertyValue($this->getFormAttribute()->getName())->modalName()
            : $this->getFormAttribute()->modalName;
    }

    protected function supportBatchDelete(): bool
    {
        return false;
    }
}

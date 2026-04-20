<?php

namespace FluxErp\Traits\Livewire\DataTable;

use FluxErp\Support\Livewire\Attributes\DataTableForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

trait DataTableHasInlineEdit
{
    public string|int|null $inlineEditingId = null;

    abstract public function loadData(bool $forceRender = false): void;

    public function bootDataTableHasInlineEdit(): void
    {
        if (! $this instanceof DataTable) {
            throw new InvalidArgumentException('This trait can only be used in a DataTable');
        }
    }

    #[Renderless]
    public function inlineEdit(string|int $id): void
    {
        $this->{$this->inlineFormAttributeName()}->reset();

        $model = resolve_static($this->model, 'query')
            ->whereKey($id)
            ->firstOrFail();

        $this->{$this->inlineFormAttributeName()}->fill($model);
        $this->inlineEditingId = $model->getKey();
    }

    #[Renderless]
    public function saveInline(): bool
    {
        try {
            $this->{$this->inlineFormAttributeName()}->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        if ($this->hasInlineSaveButton()) {
            $this->inlineEditingId = null;
        }

        return true;
    }

    #[Renderless]
    public function cancelInline(): void
    {
        $this->{$this->inlineFormAttributeName()}->reset();
        $this->inlineEditingId = null;
    }

    public function hasInlineSaveButton(): bool
    {
        return false;
    }

    protected function augmentItemArrayDataTableHasInlineEdit(array &$itemArray, Model $item): void
    {
        if (is_null($this->inlineEditingId) || $item->getKey() != $this->inlineEditingId) {
            return;
        }

        $form = $this->{$this->inlineFormAttributeName()};
        $saveOnChange = ! $this->hasInlineSaveButton();

        foreach ($form->getInlineEditableFields() as $field) {
            if (! array_key_exists($field, $itemArray)) {
                continue;
            }

            $rendered = $form->renderInlineField($field, $saveOnChange);

            if ($rendered !== '') {
                $itemArray[$field] = ['raw' => $itemArray[$field] ?? '', 'display' => $rendered];
            }
        }
    }

    protected function getRowActionsDataTableHasInlineEdit(): array
    {
        $form = $this->{$this->inlineFormAttributeName()};
        $keyName = $this->modelKeyName ?? 'id';

        return array_filter([
            DataTableButton::make()
                ->text(__('Inline Edit'))
                ->icon('pencil-square')
                ->color('emerald')
                ->when($form->canAction('update'))
                ->attributes([
                    'x-on:click' => '$wire.inlineEdit(record.' . $keyName . ')',
                    'x-show' => '$wire.inlineEditingId !== record.' . $keyName,
                    'x-cloak' => '',
                ]),
            $this->hasInlineSaveButton()
                ? DataTableButton::make()
                    ->text(__('Save'))
                    ->icon('check')
                    ->color('indigo')
                    ->when($form->canAction('update'))
                    ->attributes([
                        'x-on:click' => '$wire.saveInline()',
                        'x-show' => '$wire.inlineEditingId === record.' . $keyName,
                        'x-cloak' => '',
                    ])
                : null,
            DataTableButton::make()
                ->text(__('Cancel'))
                ->icon('x-mark')
                ->color('secondary')
                ->when($form->canAction('update'))
                ->attributes([
                    'x-on:click' => '$wire.cancelInline()',
                    'x-show' => '$wire.inlineEditingId === record.' . $keyName,
                    'x-cloak' => '',
                ]),
        ]);
    }

    protected function inlineFormAttributeName(): string
    {
        if (method_exists($this, 'formAttributeName')) {
            return $this->formAttributeName();
        }

        return $this->getAttributes()
            ->first(fn ($attribute) => $attribute instanceof DataTableForm)
            ->getName();
    }
}

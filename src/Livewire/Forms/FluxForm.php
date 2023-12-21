<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use Livewire\Form as BaseForm;

abstract class FluxForm extends BaseForm
{
    protected bool $checkPermission = true;

    abstract protected function getActions(): array;

    protected function makeAction(string $name): FluxAction
    {
        return $this->getActions()[$name]::make($this->toArray());
    }

    protected function getKey(): string
    {
        return 'id';
    }

    public function setCheckPermission(bool $checkPermission): static
    {
        $this->checkPermission = $checkPermission;

        return $this;
    }

    public function save(): void
    {
        $response = $this->makeAction($this->{$this->getKey()} ? 'update' : 'create')
            ->validate()
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->execute();

        $this->fill($response);
    }

    public function delete(): void
    {
        $this->getActions()['delete']::make($this->toArray())
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate()
            ->execute();

        $this->reset();
    }

    public function validateSave($rules = null, $messages = [], $attributes = []): void
    {
        parent::validate(
            array_intersect_key(
                array_merge(
                    $this->makeAction($this->{$this->getKey()} ? 'update' : 'create')->getRules(),
                    $rules ?? []
                ),
                $this->toArray()
            ),
            $messages,
            $attributes
        );
    }

    public function validateDelete($rules = null, $messages = [], $attributes = []): void
    {
        parent::validate(
            array_intersect_key(
                array_merge(
                    $this->makeAction('delete')->getRules(),
                    $rules ?? []
                ),
                $this->toArray()
            ),
            $messages,
            $attributes
        );
    }
}

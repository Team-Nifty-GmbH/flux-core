<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use Livewire\Form as BaseForm;

abstract class FluxForm extends BaseForm
{
    protected bool $checkPermission = true;

    protected mixed $actionResult = null;

    abstract protected function getActions(): array;

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        return $this->getActions()[$name]::make($data ?? $this->toArray());
    }

    protected function getKey(): string
    {
        return 'id';
    }

    public function getActionResult(): mixed
    {
        return $this->actionResult;
    }

    public function setCheckPermission(bool $checkPermission): static
    {
        $this->checkPermission = $checkPermission;

        return $this;
    }

    public function save(): void
    {
        if ($this->{$this->getKey()}) {
            $this->update();
        } else {
            $this->create();
        }
    }

    public function create(): void
    {
        $response = $this->makeAction('create')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate()
            ->execute();

        $this->actionResult = $response;

        $this->fill($response);
    }

    public function update(): void
    {
        $response = $this->makeAction('update')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate()
            ->execute();

        $this->actionResult = $response;

        $this->fill($response);
    }

    public function delete(): void
    {
        $response = $this->getActions()['delete']::make($this->toArray())
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate()
            ->execute();

        $this->actionResult = $response;

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

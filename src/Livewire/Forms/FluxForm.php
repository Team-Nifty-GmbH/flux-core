<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Actions\FluxAction;
use FluxErp\Support\Livewire\Attributes\ExcludeFromActionData;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Livewire\Attributes\Locked;
use Livewire\Drawer\Utils;
use Livewire\Form as BaseForm;
use ReflectionProperty;

abstract class FluxForm extends BaseForm
{
    #[ExcludeFromActionData, Locked]
    public array $loadedData = [];

    #[ExcludeFromActionData, Locked]
    public ?string $loadedModel = null;

    protected mixed $actionResult = null;

    protected bool $asyncAction = false;

    protected bool $checkPermission = true;

    protected ?string $modelClass = null;

    abstract protected function getActions(): array;

    public function async(bool $async = true): static
    {
        $this->asyncAction = $async;

        return $this;
    }

    public function canAction(string $action): bool
    {
        $actionClass = data_get($this->getActions(), $action);

        if (! is_string($actionClass)) {
            return false;
        }

        return resolve_static($actionClass, 'canPerformAction', [false]);
    }

    public function create(): void
    {
        $action = $this->makeAction('create')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate();

        if ($this->asyncAction && ! $action instanceof DispatchableFluxAction) {
            throw new InvalidArgumentException('Async actions must be DispatchableFluxAction');
        }

        if ($this->asyncAction) {
            $action->executeAsync();

            return;
        }

        $response = $action->execute();

        $this->actionResult = $response;

        $this->fill($response);
    }

    public function delete(): void
    {
        $action = $this->makeAction('delete')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate();

        if ($this->asyncAction && ! $action instanceof DispatchableFluxAction) {
            throw new InvalidArgumentException('Async actions must be DispatchableFluxAction');
        }

        if ($this->asyncAction) {
            $action->executeAsync();
            $this->reset();

            return;
        }

        $response = $action->execute();

        $this->actionResult = $response;

        $this->reset();
    }

    public function fill($values): void
    {
        parent::fill($values);

        if ($values instanceof Model) {
            $this->rememberLoadedData($values);
        }
    }

    public function getActionResult(): mixed
    {
        return $this->actionResult;
    }

    public function getModelInstance(): ?Model
    {
        if (is_null($this->modelClass)) {
            return null;
        }

        return app($this->modelClass)->query()
            ->where($this->getKey(), data_get($this, $this->getKey()))
            ->first();
    }

    public function refreshUntouchedData(): void
    {
        if (is_null($this->loadedModel)) {
            return;
        }

        $model = resolve_static($this->loadedModel, 'query')
            ->whereKey(data_get($this, $this->getKey()))
            ->first();

        if (is_null($model)) {
            return;
        }

        $current = clone $this;
        $current->fill($model);

        foreach ($this->propertyValues() as $key => $value) {
            if (array_key_exists($key, $this->loadedData) && $this->loadedData[$key] === $value) {
                $this->{$key} = $current->{$key};
            }
        }

        $this->loadedData = $current->loadedData;
    }

    public function restore(): void
    {
        $action = $this->makeAction('restore')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate();

        if ($this->asyncAction && ! $action instanceof DispatchableFluxAction) {
            throw new InvalidArgumentException('Async actions must be DispatchableFluxAction');
        }

        if ($this->asyncAction) {
            $action->executeAsync();
            $this->reset();

            return;
        }

        $response = $action->execute();

        $this->actionResult = $response;

        $this->fill($response);
    }

    public function save(): void
    {
        if ($this->{$this->getKey()}) {
            $this->update();
        } else {
            $this->create();
        }
    }

    public function setCheckPermission(bool $checkPermission): static
    {
        $this->checkPermission = $checkPermission;

        return $this;
    }

    public function toActionData(): array
    {
        return $this->propertyValues();
    }

    public function update(): void
    {
        $this->refreshUntouchedData();

        $action = $this->makeAction('update')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate();

        if ($this->asyncAction && ! $action instanceof DispatchableFluxAction) {
            throw new InvalidArgumentException('Async actions must be DispatchableFluxAction');
        }

        if ($this->asyncAction) {
            $action->executeAsync();

            return;
        }

        $response = $action->execute();

        $this->actionResult = $response;

        $this->fill($response);
    }

    public function validateDelete($rules = null, $messages = [], $attributes = []): void
    {
        parent::validate(
            array_intersect_key(
                array_merge(
                    $this->makeAction('delete')->getRules(),
                    $rules ?? []
                ),
                $this->toActionData()
            ),
            $messages,
            $attributes
        );
    }

    public function validateSave($rules = null, $messages = [], $attributes = []): void
    {
        parent::validate(
            array_intersect_key(
                array_merge(
                    $this->makeAction($this->{$this->getKey()} ? 'update' : 'create')
                        ->setRulesFromRulesets()
                        ->getRules(),
                    $rules ?? []
                ),
                $this->toActionData()
            ),
            $messages,
            $attributes
        );
    }

    protected function getKey(): string
    {
        return 'id';
    }

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        return $this->getActions()[$name]::make($data ?? $this->toActionData());
    }

    protected function propertyValues(): array
    {
        return Utils::getPublicProperties(
            $this,
            fn (ReflectionProperty $property) => collect($property->getAttributes())
                ->doesntContain(fn ($attribute) => $attribute->getName() === ExcludeFromActionData::class)
        );
    }

    protected function rememberLoadedData(Model $model): void
    {
        $this->loadedData = $this->propertyValues();
        $this->loadedModel = $model::class;
    }
}

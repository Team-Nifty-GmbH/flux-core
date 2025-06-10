<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Support\Livewire\Attributes\RenderAs;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

trait SupportsAutoRender
{
    public function autoRender(array $data): HtmlString
    {
        // $data should be passed from the blade file like this: $form->autoRender($__data)
        $properties = $this->getFilteredProperties();
        $formElements = $this->buildFormElements($properties);

        $blade = '<div class="flex flex-col gap-4">' . implode('', $formElements) . '</div>';

        if ($this->renderAsModal()) {
            $blade = $this->wrapInModal($blade);
        }

        return new HtmlString(Blade::render($blade, $data));
    }

    public function modalName(): ?string
    {
        return Str::kebab(class_basename($this)) . '-modal';
    }

    public function openModal(): void
    {
        $modalName = $this->modalName();

        $this->getComponent()
            ->js(<<<JS
                \$modalOpen('$modalName');
            JS);
    }

    protected function buildFormElements(array $properties): array
    {
        $formElements = [];

        foreach ($properties as $property) {
            $type = $this->getPropertyTypeName($property);
            $propertyName = $this->getPropertyName() . '.' . $property->getName();
            $propertyLabel = __(Str::of($property->getName())->headline()->toString());

            $formElements[] = $this->createFormElement($type, $propertyName, $propertyLabel, $property);
        }

        return $formElements;
    }

    protected function createFormElement(
        string $type,
        string $propertyName,
        string $propertyLabel,
        ?ReflectionProperty $property = null
    ): string {
        if ($property !== null) {
            $renderAsAttribute = $this->getAutoRenderAsAttribute($property);
            if ($renderAsAttribute !== null) {
                if ($renderAsAttribute->type === RenderAs::NONE) {
                    return '';
                }

                return $this->renderComponentFromAttribute($renderAsAttribute, $propertyLabel, $propertyName);
            }
        }

        $typeToElementMap = [
            'bool' => '<x-checkbox label="%s" wire:model="%s" />',
            'string' => '<x-input label="%s" wire:model="%s" />',
            'int' => '<x-number step="1" label="%s" type="number" wire:model="%s" />',
            'float' => '<x-number step="0.01" label="%s" type="number" wire:model="%s" />',
        ];

        if (str_contains($type, '|')) {
            $types = explode('|', $type);
            $typePriority = ['float', 'int', 'string', 'bool'];

            foreach ($typePriority as $priorityType) {
                if (in_array($priorityType, $types) && isset($typeToElementMap[$priorityType])) {
                    return sprintf($typeToElementMap[$priorityType], $propertyLabel, $propertyName);
                }
            }
        }

        if (! isset($typeToElementMap[$type])) {
            return '';
        }

        return sprintf($typeToElementMap[$type], $propertyLabel, $propertyName);
    }

    protected function getAutoRenderAsAttribute(ReflectionProperty $property): ?object
    {
        $attributes = $property->getAttributes(RenderAs::class);
        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    protected function getFilteredProperties(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        return array_filter($properties, function (ReflectionProperty $property) {
            return $property->isPublic()
                && ! in_array(
                    Locked::class,
                    array_map(fn ($attribute) => $attribute->getName(), $property->getAttributes())
                );
        });
    }

    protected function getPropertyTypeName(ReflectionProperty $property): string
    {
        $type = $property->getType();

        if ($type instanceof ReflectionUnionType) {
            $typeNames = [];
            foreach ($type->getTypes() as $unionType) {
                $typeNames[] = $unionType->getName();
            }

            return implode('|', $typeNames);
        }

        if ($type instanceof ReflectionNamedType && $type->allowsNull()) {
            return $type->getName() . '|null';
        }

        return $type->getName();
    }

    protected function renderAsModal(): bool
    {
        return false;
    }

    protected function renderComponentFromAttribute(object $attribute, string $propertyLabel, string $propertyName): string
    {
        $component = strtolower($attribute->type);
        $baseHtml = '<x-' . $component . ' label="%s" wire:model="%s"';

        if ($attribute->options !== null) {
            foreach ($attribute->options as $key => $value) {
                if (str_starts_with($key, ':')) {
                    $baseHtml .= ' ' . $key . '="' . $value . '"';
                } else {
                    $baseHtml .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
                }
            }
        }

        $baseHtml .= ' />';

        return sprintf($baseHtml, $propertyLabel, $propertyName);
    }

    protected function wrapInModal(string $content): string
    {
        $modalName = $this->modalName();

        return '<x-modal id="' . $modalName . '">' . $content . '
            <x-slot:footer>
                <x-button color="secondary" light flat :text="__(' . "'Cancel'" . ')" x-on:click="$modalClose(\'' . $modalName . '\')"/>
                <x-button color="indigo" :text="__(' . "'Save'" . ')" wire:click="save().then((success) => { if(success) $modalClose(\'' . $modalName . '\')})"/>
            </x-slot:footer>
        </x-modal>';
    }
}

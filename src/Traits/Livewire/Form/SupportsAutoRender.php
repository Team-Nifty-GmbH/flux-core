<?php

namespace FluxErp\Traits\Livewire\Form;

use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Support\Livewire\Attributes\RenderAs;
use FluxErp\Support\Livewire\Attributes\SeparatorAfter;
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
    protected ?string $firstInputFocusId = null;

    public function autoRender(array $data, ?array $only = null, ?array $exclude = null): HtmlString
    {
        // $data should be passed from the blade file like this: $form->autoRender($__data)
        if ($this->renderAsModal()
            && method_exists($this, 'canAction')
            && ! $this->canAction('create')
            && ! $this->canAction('update')
        ) {
            return new HtmlString('');
        }

        $this->firstInputFocusId = null;

        if (is_null($only) && is_null($exclude)) {
            $attribute = $this->getDataTableFormAttribute();
            if (! is_null($attribute)) {
                $only = $attribute->only;
                $exclude = $attribute->exclude;
            }
        }

        $properties = $this->getFilteredProperties($only, $exclude);
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
        $groupedProperties = [];
        $processedGroups = [];

        $properties = array_filter($properties, fn ($property) => $this->shouldRenderProperty($property->getName()));

        foreach ($properties as $property) {
            $renderAsAttribute = $this->getAutoRenderAsAttribute($property);
            $group = $renderAsAttribute?->group;

            if (! is_null($group)) {
                $groupedProperties[$group][] = $property;
            }
        }

        foreach ($properties as $property) {
            $renderAsAttribute = $this->getAutoRenderAsAttribute($property);
            $group = $renderAsAttribute?->group;

            if (! is_null($group) && data_get($processedGroups, $group)) {
                continue;
            }

            if (! is_null($group) && data_get($groupedProperties, $group)) {
                $groupElements = [];
                $hasSeparator = false;
                foreach ($groupedProperties[$group] as $groupProperty) {
                    $type = $this->getPropertyTypeName($groupProperty);
                    $propertyName = $this->getPropertyName() . '.' . $groupProperty->getName();
                    $propertyLabel = __(Str::of($groupProperty->getName())->headline()->toString());

                    $element = $this->createFormElement($type, $propertyName, $propertyLabel, $groupProperty);
                    if ($element !== '') {
                        $groupElements[] = '<div>' . $element . '</div>';
                    }

                    if ($this->hasSeparatorAfter($groupProperty)) {
                        $hasSeparator = true;
                    }
                }

                if (count($groupElements) > 0) {
                    $cols = min(count($groupElements), 3);

                    $gridClass = match ($cols) {
                        1 => 'grid-cols-1',
                        2 => 'grid-cols-2',
                        3 => 'grid-cols-3',
                        default => 'grid-cols-1',
                    };

                    $formElements[] = '<div class="grid ' . $gridClass . ' gap-4">' . implode('', $groupElements) . '</div>';

                    if ($hasSeparator) {
                        $formElements[] = '<hr class="my-2" />';
                    }
                }

                $processedGroups[$group] = true;

                continue;
            }

            $type = $this->getPropertyTypeName($property);
            $propertyName = $this->getPropertyName() . '.' . $property->getName();
            $propertyLabel = __(Str::of($property->getName())->headline()->toString());

            $element = $this->createFormElement($type, $propertyName, $propertyLabel, $property);
            $formElements[] = $element;

            if ($element !== '' && $this->hasSeparatorAfter($property)) {
                $formElements[] = '<hr class="my-2" />';
            }
        }

        return $formElements;
    }

    protected function shouldRenderProperty(string $propertyName): bool
    {
        return true;
    }

    protected function createFormElement(
        string $type,
        string $propertyName,
        string $propertyLabel,
        ?ReflectionProperty $property = null
    ): string {
        if (! is_null($property)) {
            $renderAsAttribute = $this->getAutoRenderAsAttribute($property);
            if (! is_null($renderAsAttribute)) {
                if ($renderAsAttribute->type === RenderAs::NONE) {
                    return '';
                }

                $label = ! is_null($renderAsAttribute->label)
                    ? __($renderAsAttribute->label)
                    : $propertyLabel;

                return $this->renderComponentFromAttribute($renderAsAttribute, $label, $propertyName);
            }
        }

        $typeToElementMap = [
            'bool' => '<x-checkbox label="%s" wire:model="%s" />',
            'string' => '<x-input label="%s" wire:model="%s"%s />',
            'int' => '<x-number step="1" label="%s" type="number" wire:model="%s"%s />',
            'float' => '<x-number step="0.01" label="%s" type="number" wire:model="%s"%s />',
        ];

        $focusAttr = '';
        $resolvedType = $type;

        if (str_contains($type, '|')) {
            $types = explode('|', $type);
            $typePriority = ['float', 'int', 'string', 'bool'];

            foreach ($typePriority as $priorityType) {
                if (in_array($priorityType, $types) && data_get($typeToElementMap, $priorityType)) {
                    $resolvedType = $priorityType;
                    break;
                }
            }
        }

        if (! data_get($typeToElementMap, $resolvedType)) {
            return '';
        }

        if (is_null($this->firstInputFocusId) && $this->isFocusableBuiltinType($resolvedType)) {
            $this->firstInputFocusId = $this->modalName() . '-focus';
            $focusAttr = ' data-focus="' . $this->firstInputFocusId . '"';
        }

        if ($resolvedType === 'bool') {
            return sprintf($typeToElementMap[$resolvedType], $propertyLabel, $propertyName);
        }

        return sprintf($typeToElementMap[$resolvedType], $propertyLabel, $propertyName, $focusAttr);
    }

    protected function getAutoRenderAsAttribute(ReflectionProperty $property): ?object
    {
        $attributes = $property->getAttributes(RenderAs::class);
        if (! $attributes) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    protected function hasSeparatorAfter(ReflectionProperty $property): bool
    {
        return (bool) $property->getAttributes(SeparatorAfter::class);
    }

    protected function getDataTableFormAttribute(): ?DataTableForm
    {
        $component = $this->getComponent();
        $propertyName = $this->getPropertyName();

        $reflection = new ReflectionClass($component);

        if (! $reflection->hasProperty($propertyName)) {
            return null;
        }

        $property = $reflection->getProperty($propertyName);
        $attributes = $property->getAttributes(DataTableForm::class);

        if (! $attributes) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    protected function getFilteredProperties(?array $only = null, ?array $exclude = null): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        return array_filter($properties, function (ReflectionProperty $property) use ($only, $exclude) {
            $propertyName = $property->getName();

            if (! is_null($only) && ! in_array($propertyName, $only)) {
                return false;
            }

            if (! is_null($exclude) && in_array($propertyName, $exclude)) {
                return false;
            }

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

        if (is_null($this->firstInputFocusId) && $this->isFocusableComponent($attribute->type)) {
            $this->firstInputFocusId = $this->modalName() . '-focus';
            $baseHtml .= ' data-focus="' . $this->firstInputFocusId . '"';
        }

        if (! is_null($attribute->options)) {
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

    protected function isModalPersistent(): bool
    {
        return true;
    }

    protected function wrapInModal(string $content): string
    {
        $modalName = $this->modalName();
        $saveMethod = $this->getSaveMethod();
        $deleteMethod = $this->getDeleteMethod();
        $persistent = $this->isModalPersistent() ? ' persistent' : '';
        $focusOn = ! is_null($this->firstInputFocusId)
            ? ' x-on:open="$focusOn(\'' . $this->firstInputFocusId . '\')"'
            : '';

        $deleteButton = '';
        if ($deleteMethod) {
            $formProperty = $this->getPropertyName();
            $deleteButton = '<x-button x-cloak x-show="$wire.' . $formProperty . '.id" color="red" '
                . ':text="__(' . "'Delete'" . ')" '
                . 'wire:flux-confirm.type.error="{{ __(\'wire:confirm.delete\', [\'model\' => \''
                . class_basename($this) . '\']) }}" '
                . 'wire:click="' . $deleteMethod . '().then((success) => { if(success) $modalClose(\''
                . $modalName . '\')})"/>';
        }

        $saveAction = $saveMethod . '().then((success) => { if(success) $modalClose(\'' . $modalName . '\')})';
        $cancelAction = '$modalClose(\'' . $modalName . '\')';

        return '<div x-on:keydown.enter.prevent="$wire.' . $saveAction . '"'
            . ' x-on:keydown.escape.prevent="' . $cancelAction . '">'
            . '<x-modal id="' . $modalName . '"' . $persistent . $focusOn . '>'
            . $content
            . '<x-slot:footer>'
            . '<div class="flex w-full justify-between">'
            . '<div>' . $deleteButton . '</div>'
            . '<div class="flex gap-2">'
            . '<x-button color="secondary" light flat :text="__(' . "'Cancel'" . ')" x-on:click="' . $cancelAction . '"/>'
            . '<x-button color="indigo" :text="__(' . "'Save'" . ')" wire:click="' . $saveAction . '"/>'
            . '</div>'
            . '</div>'
            . '</x-slot:footer>'
            . '</x-modal>'
            . '</div>';
    }

    protected function getSaveMethod(): string
    {
        $attribute = $this->getDataTableFormAttribute();

        return $attribute?->saveMethod ?? 'save';
    }

    protected function getDeleteMethod(): ?string
    {
        $attribute = $this->getDataTableFormAttribute();

        return $attribute?->deleteMethod;
    }

    protected function isFocusableComponent(string $type): bool
    {
        $focusableTypes = [
            RenderAs::INPUT,
            RenderAs::NUMBER,
            RenderAs::PASSWORD,
            RenderAs::TEXTAREA,
            RenderAs::PIN,
            RenderAs::COLOR,
        ];

        return in_array($type, $focusableTypes);
    }

    protected function isFocusableBuiltinType(string $type): bool
    {
        return in_array($type, ['string', 'int', 'float']);
    }
}

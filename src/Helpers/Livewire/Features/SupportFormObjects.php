<?php

namespace FluxErp\Helpers\Livewire\Features;

use Livewire\Features\SupportFormObjects\Form;
use Livewire\Features\SupportFormObjects\FormObjectSynth;
use Livewire\Features\SupportFormObjects\SupportFormObjects as BaseSupportFormObjects;
use ReflectionClass;
use ReflectionNamedType;

class SupportFormObjects extends BaseSupportFormObjects
{
    protected function initializeFormObjects(): void
    {
        foreach ((new ReflectionClass($this->component))->getProperties() as $property) {
            // Public properties only...
            if ($property->isPublic() !== true) {
                continue;
            }
            // Uninitialized properties only...
            if ($property->isInitialized($this->component)) {
                continue;
            }

            $type = $property->getType();

            if (! $type instanceof ReflectionNamedType) {
                continue;
            }

            $typeName = resolve_static($type->getName(), 'class');

            // "Form" object property types only...
            if (! is_subclass_of($typeName, Form::class)) {
                continue;
            }

            $form = new $typeName(
                $this->component,
                $name = $property->getName()
            );

            $callBootMethod = FormObjectSynth::bootFormObject($this->component, $form, $name);

            $property->setValue($this->component, $form);

            $callBootMethod();
        }
    }
}

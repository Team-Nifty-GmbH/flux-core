<?php

namespace FluxErp\Traits;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;

trait DropdownButtonTrait
{
    use EditorButtonTrait;

    public function render(): string
    {
        $ref = $this->dropdownRef();

        $attributes = new ComponentAttributeBag([
            'flat' => true,
            'color' => 'secondary',
            'x-on:click.prevent' => 'onClick',
            'x-ref' => "floatingUiParent-$ref",
            'x-data' => "floatingUiDropdown(\$refs['floatingUiParent-$ref'], \$refs['$ref" . "Dropdown'])",
        ]);

        if ($icon = $this->icon()) {
            $attributes = $attributes->merge(['icon' => $icon]);
        }

        if ($displayTitle = $this->title() ?? ($this->tooltip() ? __($this->tooltip()) : null)) {
            $attributes = $attributes->merge(['title' => $displayTitle]);
        }

        if ($text = $this->text()) {
            return Blade::render(<<<'Blade'
                <x-button {{ $attributes }}>
                    {!! $text !!}
                </x-button>
            Blade, ['attributes' => $attributes, 'text' => $text]);
        }

        return Blade::render(<<<'Blade'
            <x-button {{ $attributes }} />
        Blade, ['attributes' => $attributes]);
    }

    public function dropdownRef(): string
    {
        return Str::kebab(class_basename(static::class));
    }

    public function command(): ?string
    {
        return null;
    }
}

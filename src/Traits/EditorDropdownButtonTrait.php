<?php

namespace FluxErp\Traits;

use FluxErp\Contracts\EditorTooltipButton;
use Illuminate\Support\Facades\Blade;

trait EditorDropdownButtonTrait
{
    use EditorButtonTrait;

    public function render(): string
    {
        $ref = $this->dropdownRef();
        $isCircle = $this instanceof EditorTooltipButton && $this->editor?->tooltipDropdown;

        $attributes = $this->buttonAttributes()->merge([
            'x-on:click.prevent' => 'onClick',
            'x-ref' => $isCircle ? "floatingUiTooltip-$ref" : "floatingUiParent-$ref",
        ]);

        if ($isCircle) {
            return Blade::render(
                <<<'Blade'
                    <div x-data="floatingUiDropdown($refs['floatingUiTooltip-{{ $ref }}'], () => $refs['{{ $ref }}Dropdown'])">
                        <x-button.circle {{ $attributes }} />
                    </div>
                Blade,
                [
                    'ref' => $ref,
                    'attributes' => $attributes,
                ]
            );
        }

        $attributes = $attributes->merge([
            'x-data' => "floatingUiDropdown(\$refs['floatingUiParent-$ref'], \$refs['{$ref}Dropdown'])",
        ]);

        if ($text = $this->text()) {
            return Blade::render(
                <<<'Blade'
                    <x-button {{ $attributes }}>
                        {!! $text !!}
                    </x-button>
                Blade,
                [
                    'attributes' => $attributes,
                    'text' => $text,
                ]
            );
        }

        return Blade::render(
            <<<'Blade'
                <x-button {{ $attributes }} />
            Blade,
            [
                'attributes' => $attributes,
            ]
        );
    }

    public function dropdownRef(): string
    {
        return static::identifier()->toString();
    }

    public function command(): ?string
    {
        return null;
    }
}

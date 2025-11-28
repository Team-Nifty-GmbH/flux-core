<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorDropdownButton;
use FluxErp\Contracts\EditorTooltipButton;
use FluxErp\Traits\EditorDropdownButtonTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Js;
use Illuminate\View\Component;

class BladeVariables extends Component implements EditorDropdownButton, EditorTooltipButton
{
    use EditorDropdownButtonTrait {
        EditorDropdownButtonTrait::render as traitRender;
    }

    public function render(): string|View
    {
        if (! $this->editor?->bladeVariables) {
            return '';
        }

        return $this->traitRender();
    }

    public function icon(): ?string
    {
        return 'variable';
    }

    public function tooltip(): ?string
    {
        return 'Variables';
    }

    public function command(): ?string
    {
        return null;
    }

    public function isActive(): ?string
    {
        return null;
    }

    public function dropdownContent(): array
    {
        $buttons = [];

        if (! $this->editor) {
            return $buttons;
        }

        foreach ($this->editor->bladeVariables as $variable) {
            $buttons[] = app(
                DropdownItem::class,
                [
                    'text' => e($variable['label']),
                    'command' => 'editor()
                            .chain()
                            .focus()
                            .insertContent([
                            {
                                type: \'bladeVariable\',
                                attrs: {
                                    label: ' . Js::from($variable['label']) . ',
                                    value: ' . Js::from($variable['value']) . '
                                }
                            },
                            {
                                type: \'text\',
                                text: \' \'
                            }
                        ])
                        .run()',
                ]
            );
        }

        return $buttons;
    }
}

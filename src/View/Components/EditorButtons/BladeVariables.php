<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorDropdownButton;
use FluxErp\Contracts\EditorTooltipButton;
use FluxErp\Traits\Editor\EditorDropdownButtonTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
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
        return [
            new HtmlString(<<<'HTML'
                <template x-for="variable in Object.values(bladeVariables || {})" :key="variable.value">
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 rounded p-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                        x-on:click.prevent="editor().chain().focus().insertContent([{ type: 'bladeVariable', attrs: { label: variable.label, value: variable.value } }, { type: 'text', text: ' ' }]).run()"
                        x-text="variable.label"
                    ></button>
                </template>
                HTML),
        ];
    }
}

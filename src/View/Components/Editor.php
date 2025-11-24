<?php

namespace FluxErp\View\Components;

use Closure;
use FluxErp\Contracts\EditorDropdownButton;
use FluxErp\Facades\Editor as EditorFacade;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Editor extends Component
{
    public function __construct(
        public ?string $id = null,
        public ?array $buttons = null,
        public ?string $scope = null,
        public bool $tooltipDropdown = false,
        public bool $transparent = false,
        public bool $fullHeight = false,
        public bool $showEditorPadding = true,
        public array $bladeVariables = [],
    ) {
        $this->id ??= Str::uuid()->toString();
    }

    public function render(): View|Closure|string
    {
        return view('flux::components.editor', [
            'buttonInstances' => $buttonInstances = $this->getButtonInstances(),
            'tooltipDropdownContent' => $this->getTooltipDropdownContent($buttonInstances),
        ]);
    }

    public function shouldShowButton(string $buttonIdentifier): bool
    {
        if (! is_null($this->buttons)) {
            return in_array($buttonIdentifier, $this->buttons, true);
        }

        return true;
    }

    public function getButtonInstance(string $buttonClass): mixed
    {
        $buttonIdentifier = $buttonClass::identifier();

        if (! $this->shouldShowButton($buttonIdentifier)) {
            return null;
        }

        $scopes = $buttonClass::scopes();
        if ($scopes && ! is_null($this->scope) && ! in_array($this->scope, $scopes, true)) {
            return null;
        }

        return app($buttonClass);
    }

    public function getButtonInstances(): array
    {
        $instances = [];

        foreach (EditorFacade::getButtons() as $buttonClass) {
            $instance = $this->getButtonInstance($buttonClass)?->setScope($this->scope);
            if (! is_null($instance)) {
                $instances[] = $instance;
            }
        }

        return $instances;
    }

    public function getTooltipDropdownContent(array $buttonInstances): array
    {
        $content = [];

        foreach ($buttonInstances as $instance) {
            if ($instance instanceof EditorDropdownButton) {
                $this->tooltipDropdown = true;
                $content = array_merge($content, $instance->dropdownContent());
            }
        }

        return $content;
    }
}

<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\EditorButtonTrait;
use Illuminate\View\Component;

class DropdownItem extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function __construct(
        private ?string $text = null,
        private ?string $icon = null,
        private ?string $command = null,
        private ?string $isActive = null,
        private array $additionalAttributes = [],
    ) {}

    public function command(): ?string
    {
        return $this->command;
    }

    public function isActive(): ?string
    {
        return $this->isActive;
    }

    public function icon(): ?string
    {
        return $this->icon;
    }

    public function text(): ?string
    {
        return $this->text;
    }

    public function attributes(): array
    {
        return $this->additionalAttributes;
    }
}

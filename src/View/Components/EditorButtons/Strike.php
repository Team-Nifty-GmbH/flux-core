<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\EditorButtonTrait;
use Illuminate\View\Component;

class Strike extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function icon(): ?string
    {
        return null;
    }

    public function text(): ?string
    {
        return 'S';
    }

    public function tooltip(): ?string
    {
        return 'Strikethrough';
    }

    protected function attributes(): array
    {
        return [
            'class' => 'line-through',
        ];
    }
}

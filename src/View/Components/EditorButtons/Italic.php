<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\EditorButtonTrait;
use Illuminate\View\Component;

class Italic extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function text(): ?string
    {
        return 'I';
    }

    public function tooltip(): ?string
    {
        return 'Italic';
    }

    protected function attributes(): array
    {
        return [
            'class' => 'font-italic',
        ];
    }
}

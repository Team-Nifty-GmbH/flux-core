<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\Editor\EditorButtonTrait;
use Illuminate\View\Component;

class Bold extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function text(): ?string
    {
        return 'B';
    }

    public function tooltip(): ?string
    {
        return 'Bold';
    }

    protected function attributes(): array
    {
        return [
            'class' => 'font-bold',
        ];
    }
}

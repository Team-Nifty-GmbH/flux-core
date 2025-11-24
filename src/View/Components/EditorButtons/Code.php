<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\EditorButtonTrait;
use Illuminate\View\Component;

class Code extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function icon(): ?string
    {
        return 'code-bracket';
    }

    public function tooltip(): ?string
    {
        return 'Inline Code';
    }
}

<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\Editor\EditorButtonTrait;
use Illuminate\View\Component;

class CodeBlock extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function icon(): ?string
    {
        return 'code-bracket-square';
    }

    public function tooltip(): ?string
    {
        return 'Code Block';
    }
}

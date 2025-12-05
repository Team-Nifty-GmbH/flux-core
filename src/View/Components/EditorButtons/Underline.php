<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\Editor\EditorButtonTrait;
use Illuminate\View\Component;

class Underline extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function icon(): ?string
    {
        return null;
    }

    public function text(): ?string
    {
        return 'U';
    }

    public function tooltip(): ?string
    {
        return 'Underline';
    }

    protected function attributes(): array
    {
        return [
            'class' => 'underline',
        ];
    }
}

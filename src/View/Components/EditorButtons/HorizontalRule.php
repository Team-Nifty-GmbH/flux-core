<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\EditorButtonTrait;
use Illuminate\View\Component;

class HorizontalRule extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function command(): ?string
    {
        return <<<'JS'
            editor().chain().focus().setHorizontalRule().run()
            JS;
    }

    public function icon(): ?string
    {
        return null;
    }

    public function text(): ?string
    {
        return '-';
    }

    public function tooltip(): ?string
    {
        return 'Horizontal Rule';
    }
}

<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\Editor\EditorButtonTrait;
use Illuminate\View\Component;

class AlignLeft extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function command(): ?string
    {
        return <<<'JS'
            editor().chain().focus().setTextAlign('left').run()
            JS;
    }

    public function isActive(): ?string
    {
        return <<<'JS'
            editor().isActive({ textAlign: 'left' }) ? 'bg-primary-100 dark:bg-primary-900' : ''
            JS;
    }

    public function icon(): ?string
    {
        return 'bars-3-bottom-left';
    }

    public function tooltip(): ?string
    {
        return 'Align Left';
    }
}

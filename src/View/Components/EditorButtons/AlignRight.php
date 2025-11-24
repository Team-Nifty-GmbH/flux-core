<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\EditorButtonTrait;
use Illuminate\View\Component;

class AlignRight extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function command(): ?string
    {
        return <<<'JS'
            editor().chain().focus().setTextAlign('right').run()
            JS;
    }

    public function isActive(): ?string
    {
        return <<<'JS'
            editor().isActive({ textAlign: 'right' }) ? 'bg-primary-100 dark:bg-primary-900' : ''
            JS;
    }

    public function icon(): ?string
    {
        return 'bars-3-bottom-right';
    }

    public function tooltip(): ?string
    {
        return 'Align Right';
    }
}

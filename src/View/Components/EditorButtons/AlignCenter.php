<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\EditorButtonTrait;
use Illuminate\View\Component;

class AlignCenter extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function command(): ?string
    {
        return <<<'JS'
            editor().chain().focus().setTextAlign('center').run()
            JS;
    }

    public function isActive(): ?string
    {
        return <<<'JS'
            editor().isActive({ textAlign: 'center' }) ? 'bg-primary-100 dark:bg-primary-900' : ''
            JS;
    }

    public function icon(): ?string
    {
        return 'bars-3';
    }

    public function tooltip(): ?string
    {
        return 'Align Center';
    }
}

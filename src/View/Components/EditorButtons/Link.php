<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\EditorButtonTrait;
use Illuminate\View\Component;

class Link extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function command(): ?string
    {
        return <<<'JS'
            (() => {
                if (editor().isActive('link')) {
                    editor().chain().focus().unsetLink().run();
                } else {
                    const url = prompt('URL eingeben:');
                    if (url) {
                        editor().chain().focus().setLink({ href: url }).run();
                    }
                }
            })()
            JS;
    }

    public function isActive(): ?string
    {
        return <<<'JS'
            editor().isActive('link')
            JS;
    }

    public function icon(): ?string
    {
        return 'link';
    }

    public function tooltip(): ?string
    {
        return 'Link';
    }
}

<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\Editor\EditorButtonTrait;
use Illuminate\View\Component;

class OrderedList extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function icon(): ?string
    {
        return 'numbered-list';
    }

    public function tooltip(): ?string
    {
        return 'Ordered List';
    }
}

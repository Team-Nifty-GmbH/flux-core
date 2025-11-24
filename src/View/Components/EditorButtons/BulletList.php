<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\EditorButtonTrait;
use Illuminate\View\Component;

class BulletList extends Component implements EditorButton
{
    use EditorButtonTrait;


    public function icon(): ?string
    {
        return 'list-bullet';
    }

    public function tooltip(): ?string
    {
        return 'Bullet List';
    }
}

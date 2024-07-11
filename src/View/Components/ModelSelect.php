<?php

namespace FluxErp\View\Components;

use WireUi\Components\Select\Base;

class ModelSelect extends Base
{
    protected function getView(): string
    {
        return 'flux::components.model-select';
    }
}

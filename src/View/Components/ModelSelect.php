<?php

namespace FluxErp\View\Components;

use WireUi\View\Components\Select;

class ModelSelect extends Select
{
    protected function getView(): string
    {
        return 'flux::components.model-select';
    }
}

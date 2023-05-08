<?php

namespace FluxErp\Traits;

use FluxErp\Models\Widget;

trait HasWidgets
{
    public function widgets()
    {
        return $this->morphMany(Widget::class, 'widgetable');
    }
}

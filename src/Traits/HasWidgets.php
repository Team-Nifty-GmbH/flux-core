<?php

namespace FluxErp\Traits;

use FluxErp\Models\Widget;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasWidgets
{
    public function widgets(): MorphMany
    {
        return $this->morphMany(Widget::class, 'widgetable');
    }
}

<?php

namespace FluxErp\Traits\Model;

use FluxErp\Models\Widget;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasWidgets
{
    /**
     * @return MorphMany<Widget, $this>
     */
    public function widgets(): MorphMany
    {
        return $this->morphMany(Widget::class, 'widgetable');
    }
}

<?php

namespace FluxErp\Traits\Model;

use FluxErp\Models\Cart;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCart
{
    public function carts(): MorphMany
    {
        return $this->morphMany(Cart::class, 'authenticatable');
    }
}

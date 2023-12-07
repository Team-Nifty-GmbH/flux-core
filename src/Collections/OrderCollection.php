<?php

namespace FluxErp\Collections;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Traits\Printable;
use Illuminate\Database\Eloquent\Collection;

class OrderCollection extends Collection implements OffersPrinting
{
    use Printable;

    public function getPrintViews(): array
    {
        return [];
    }
}

<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum ConfigurationPriceModeEnum: string
{
    use EnumTrait;

    case Surcharge = 'surcharge';

    case ProductPrice = 'product_price';
}

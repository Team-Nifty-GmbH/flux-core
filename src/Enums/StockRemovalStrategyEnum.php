<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum StockRemovalStrategyEnum: string
{
    use EnumTrait;

    case Fefo = 'fefo';

    case Fifo = 'fifo';

    case Lifo = 'lifo';
}

<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum WarehouseBinTypeEnum: string
{
    use EnumTrait;

    case Aisle = 'aisle';

    case Bin = 'bin';

    case GoodsIn = 'goods-in';

    case GoodsOut = 'goods-out';

    case Packing = 'packing';

    case Quarantine = 'quarantine';

    case Rack = 'rack';

    case Shelf = 'shelf';

    case Zone = 'zone';
}

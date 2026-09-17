<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum StorageAreaTypeEnum: string
{
    use EnumTrait;

    case Aisle = 'aisle';

    case Container = 'container';

    case GoodsIn = 'goods-in';

    case GoodsOut = 'goods-out';

    case Packing = 'packing';

    case Quarantine = 'quarantine';

    case Rack = 'rack';

    case Shelf = 'shelf';

    case Zone = 'zone';
}

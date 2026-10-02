<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class StorageAreaTypeEnum extends FluxEnum
{
    use EnumTrait;

    final public const string Aisle = 'aisle';

    final public const string Container = 'container';

    final public const string GoodsIn = 'goods-in';

    final public const string GoodsOut = 'goods-out';

    final public const string Packing = 'packing';

    final public const string Quarantine = 'quarantine';

    final public const string Rack = 'rack';

    final public const string Shelf = 'shelf';

    final public const string Zone = 'zone';
}

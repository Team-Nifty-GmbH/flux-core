<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUuid;
use Illuminate\Database\Eloquent\Casts\AsFluent;

class Widget extends FluxModel
{
    use HasPackageFactory, HasUuid;

    protected function casts(): array
    {
        return [
            'config' => AsFluent::class,
        ];
    }
}

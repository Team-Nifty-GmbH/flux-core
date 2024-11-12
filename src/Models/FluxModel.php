<?php

namespace FluxErp\Models;

use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Model;

abstract class FluxModel extends Model
{
    use ResolvesRelationsThroughContainer;
}

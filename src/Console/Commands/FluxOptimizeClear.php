<?php

namespace FluxErp\Console\Commands;

use FluxErp\Facades\Action;
use FluxErp\Facades\Repeatable;
use FluxErp\Traits\HasDefault;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FluxOptimizeClear extends FluxOptimize
{
    protected $signature = 'flux:optimize:clear';

    protected bool $forget = true;
}

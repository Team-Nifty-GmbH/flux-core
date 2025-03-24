<?php

namespace FluxErp\Console\Commands;

class FluxOptimizeClear extends FluxOptimize
{
    protected bool $forget = true;

    protected $signature = 'flux:optimize-clear';
}

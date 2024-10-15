<?php

namespace FluxErp\Console\Commands;

class FluxOptimizeClear extends FluxOptimize
{
    protected $signature = 'flux:optimize-clear';

    protected bool $forget = true;
}

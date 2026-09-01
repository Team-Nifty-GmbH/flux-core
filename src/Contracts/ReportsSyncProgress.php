<?php

namespace FluxErp\Contracts;

use Closure;

interface ReportsSyncProgress
{
    /**
     * Register a callback that is invoked with the number of processed
     * messages and the total message count while syncing a folder.
     */
    public function withProgressCallback(?Closure $callback): static;
}

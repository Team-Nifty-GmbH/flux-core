<?php

namespace FluxErp\Actions;

use FluxErp\Contracts\ShouldBeMonitored;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

abstract class DispatchableFluxAction extends FluxAction implements ShouldBeMonitored, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    final public function executeAsync(): void
    {
        static::dispatch($this->data, $this->keepEmptyStrings);
    }

    public function handle(): mixed
    {
        return $this->validate()->execute();
    }

    protected function nonSerializableProperties(): array
    {
        return array_merge(parent::nonSerializableProperties(), [
            'fakeBatch',
            'job',
        ]);
    }
}

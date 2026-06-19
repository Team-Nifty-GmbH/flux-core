<?php

namespace FluxErp\Support\Bus;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Contracts\SupportsBulkExecution;
use FluxErp\Traits\Makeable;
use Illuminate\Support\Facades\Bus;
use InvalidArgumentException;

/**
 * Runs a single bulk-capable action across many records as one monitored batch.
 *
 * Each payload is a full action data array. The action class must extend
 * {@see DispatchableFluxAction} and implement {@see SupportsBulkExecution};
 * every payload is dispatched as its own queued job inside a single
 * {@see MonitorablePendingBatch} so the user sees one progress toast.
 */
class BulkExecutor
{
    use Makeable;

    protected ?string $name = null;

    /**
     * @param  class-string  $action
     * @param  array<int, array<string, mixed>>  $payloads
     */
    public function __construct(
        protected string $action,
        protected array $payloads,
    ) {}

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function dispatch(): void
    {
        if (
            ! is_a($this->action, DispatchableFluxAction::class, true)
            || ! is_a($this->action, SupportsBulkExecution::class, true)
        ) {
            throw new InvalidArgumentException(
                $this->action . ' must extend ' . DispatchableFluxAction::class
                . ' and implement ' . SupportsBulkExecution::class . ' to be executed in bulk.'
            );
        }

        if (blank($this->payloads)) {
            return;
        }

        $jobs = array_map(
            fn (array $payload) => $this->action::make($payload)
                ->checkPermission()
                ->validate(),
            $this->payloads,
        );

        Bus::monitoredBatch($jobs)
            ->name($this->name ?? class_basename($this->action))
            ->allowFailures()
            ->dispatch();
    }
}

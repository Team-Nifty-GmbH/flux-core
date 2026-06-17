<?php

namespace FluxErp\Jobs;

use FluxErp\Actions\FluxAction;
use FluxErp\Contracts\ShouldBeMonitored;
use FluxErp\Traits\IsMonitored;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;
use Throwable;

class ExecuteActionsJob implements ShouldBeMonitored, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable;

    /**
     * Each payload entry is a full action data array or a scalar treated as the action's id.
     *
     * @param  class-string<FluxAction>  $action
     * @param  array<int, array<string, mixed>|int|string>  $payloads
     */
    public function __construct(
        protected string $action,
        protected array $payloads,
        protected ?string $name = null,
    ) {}

    public function getName(): string
    {
        return $this->name ?? __(Str::headline(class_basename($this->action)));
    }

    public function handle(): void
    {
        if (! is_a($this->action, FluxAction::class, true)) {
            return;
        }

        $total = count($this->payloads);

        if ($total === 0) {
            return;
        }

        $done = 0;
        $failed = 0;

        foreach (array_values($this->payloads) as $index => $payload) {
            try {
                $this->action::make(is_array($payload) ? $payload : ['id' => $payload])
                    ->checkPermission()
                    ->validate()
                    ->execute();

                $done++;
            } catch (Throwable $e) {
                report($e);

                $failed++;
            }

            $this->queueProgress((int) (($index + 1) / $total * 100));
        }

        $this->message(trim(
            __(':done of :total done', ['done' => $done, 'total' => $total])
            . ($failed > 0 ? ' · ' . __(':failed failed', ['failed' => $failed]) : '')
        ));
    }
}

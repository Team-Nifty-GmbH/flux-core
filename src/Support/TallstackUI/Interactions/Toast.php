<?php

namespace FluxErp\Support\TallstackUI\Interactions;

use FluxErp\Traits\Makeable;
use Illuminate\Support\Traits\Conditionable;
use TallStackUi\Interactions\Toast as BaseToast;

class Toast extends BaseToast
{
    use Conditionable, Makeable;

    protected string $eventName = 'toast';

    protected int|string|null $id = null;

    protected ?float $progress = null;

    public function id(int|string|null $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function progress(float|int|string|null $progress): static
    {
        $this->progress = $progress === null ? 0.0 : (float) $progress;

        return $this;
    }

    public function setEventName(string $eventName): static
    {
        $this->eventName = $eventName;

        return $this;
    }

    protected function additional(): array
    {
        return array_merge(
            parent::additional(),
            [
                'progress' => $this->progress,
                'toastId' => $this->id,
            ]
        );
    }

    protected function event(): string
    {
        return $this->eventName;
    }
}

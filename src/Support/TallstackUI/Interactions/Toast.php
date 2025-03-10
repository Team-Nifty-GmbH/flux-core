<?php

namespace FluxErp\Support\TallstackUI\Interactions;

use FluxErp\Traits\Makeable;
use Illuminate\Support\Traits\Conditionable;
use TallStackUi\Foundation\Interactions\Toast as BaseToast;

class Toast extends BaseToast
{
    use Conditionable, Makeable;

    protected string $eventName = 'toast';

    protected ?int $progress = null;

    protected int|string|null $id = null;

    public function progress(int $progress): static
    {
        $this->progress = $progress;

        return $this;
    }

    public function setEventName(string $eventName): static
    {
        $this->eventName = $eventName;

        return $this;
    }

    protected function event(): string
    {
        return $this->eventName;
    }

    public function id(int|string|null $id): static
    {
        $this->id = $id;

        return $this;
    }

    protected function additional(): array
    {
        return [
            'expandable' => $this->expand ?? config('tallstackui.settings.toast.expandable', false),
            'timeout' => $this->timeout,
            'persistent' => $this->persistent,
            'progress' => $this->progress,
            'toastId' => $this->id,
        ];
    }
}

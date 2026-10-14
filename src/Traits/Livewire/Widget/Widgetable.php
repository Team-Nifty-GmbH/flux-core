<?php

namespace FluxErp\Traits\Livewire\Widget;

use FluxErp\Models\Contact;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;

trait Widgetable
{
    #[Locked]
    public ?string $dashboardComponent = null;

    abstract public static function dashboardComponent(): array|string;

    public static function getCategory(): ?string
    {
        return null;
    }

    public static function getLabel(): string
    {
        if (app()->runningInConsole()) {
            return Str::headline(class_basename(static::class));
        }

        return __(Str::headline(class_basename(static::class)));
    }

    public function mountWidgetable(): void
    {
        if (! $this->isDateDependent()) {
            return;
        }

        $this->js(
            <<<JS
                const reloadAtMidnight = () => {
                    const midnight = new Date();
                    midnight.setHours(24, 0, 5, 0);

                    setTimeout(() => {
                        if (! \$wire.\$el.isConnected) {
                            return;
                        }

                        \$wire.call('{$this->liveUpdateMethod()}');
                        reloadAtMidnight();
                    }, midnight - new Date());
                };

                reloadAtMidnight();
            JS
        );
    }

    public function placeholder(): View
    {
        if (method_exists(parent::class, 'placeholder')) {
            return parent::placeholder();
        }

        return view('flux::livewire.placeholders.box');
    }

    public function showTitle(): bool
    {
        return true;
    }

    protected function contactChannel(int|string $contactId): string
    {
        return morph_alias(Contact::class) . '.' . $contactId;
    }

    protected function getListeners(): array
    {
        $listeners = parent::getListeners();
        $method = $this->liveUpdateMethod();

        foreach ($this->liveUpdates() as $channel => $models) {
            foreach ($models as $model => $events) {
                $eventPrefix = class_basename(resolve_static($model, 'class'));

                foreach ($events as $event) {
                    $listeners['echo-private:' . $channel . ',.' . $eventPrefix . ucfirst($event)] = $method;
                }
            }
        }

        return $listeners;
    }

    protected function isDateDependent(): bool
    {
        return false;
    }

    protected function liveUpdateMethod(): string
    {
        return match (true) {
            method_exists($this, 'calculateByTimeFrame') => 'calculateByTimeFrame',
            method_exists($this, 'loadData') => 'loadData',
            default => '$refresh',
        };
    }

    protected function liveUpdates(): array
    {
        return [];
    }

    protected function modelChannel(string $model): string
    {
        return resolve_static($model, 'getBroadcastChannel');
    }

    protected function userChannel(): string
    {
        return auth()->user()->broadcastChannel();
    }
}

<?php

namespace FluxErp\Traits\Livewire\Widget;

use FluxErp\Support\Auth\PermissionSet;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Spatie\Permission\Exceptions\UnauthorizedException;

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

    public function hydrateWidgetable(): void
    {
        $this->ensureWidgetPermission();
    }

    public function mountWidgetable(): void
    {
        $this->ensureWidgetPermission();
        $this->subscribeLiveUpdates();
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

    protected function ensureWidgetPermission(): void
    {
        if (is_null($this->dashboardComponent)) {
            return;
        }

        $permission = 'widget.' . $this->getName();

        if (! PermissionSet::make()->allows($permission)) {
            throw UnauthorizedException::forPermissions([$permission]);
        }
    }

    protected function isDateDependent(): bool
    {
        return in_array(IsTimeFrameAwareWidget::class, class_uses_recursive($this));
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

    protected function liveUpdateListeners(): array
    {
        $listeners = [];

        foreach ($this->liveUpdates() as $channel => $models) {
            if (blank($channel)) {
                continue;
            }

            foreach ($models as $model => $events) {
                $eventPrefix = class_basename(resolve_static($model, 'class'));

                if (in_array('deleted', $events)) {
                    $events[] = 'restored';
                }

                foreach (array_unique($events) as $event) {
                    $listeners[] = [$channel, '.' . $eventPrefix . ucfirst($event)];
                }
            }
        }

        return $listeners;
    }

    protected function modelChannel(string $model, int|string|null $key = null): string
    {
        return is_null($key)
            ? resolve_static($model, 'getBroadcastChannel')
            : resolve_static($model, 'recordBroadcastChannel', ['key' => $key]);
    }

    protected function subscribeLiveUpdates(): void
    {
        $listeners = $this->liveUpdateListeners();
        $midnightIn = $this->isDateDependent()
            ? now()->addDay()->startOfDay()->getTimestampMs() - now()->getTimestampMs() + random_int(5000, 30000)
            : null;

        if (! $listeners && is_null($midnightIn)) {
            return;
        }

        $this->js(
            'window.fluxWidgetLiveUpdates($wire, ' . json_encode([
                'method' => $this->liveUpdateMethod(),
                'listeners' => $listeners,
                'midnightIn' => $midnightIn,
            ]) . ')'
        );
    }
}

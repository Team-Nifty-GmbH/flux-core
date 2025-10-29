<?php

namespace FluxErp\Traits\Action;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\NullDispatcher;
use Illuminate\Events\QueuedClosure;

trait HasActionEvents
{
    protected ?Dispatcher $dispatcher = null;

    public static function booted(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('booted', $callback);
    }

    public static function booting(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('booting', $callback);
    }

    public static function executed(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('executed', $callback);
    }

    public static function executing(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('executing', $callback);
    }

    public static function preparingForValidation(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('preparingForValidation', $callback);
    }

    public static function validated(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('validated', $callback);
    }

    public static function validating(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('validating', $callback);
    }

    protected static function registerActionEvent($event, $callback): void
    {
        if (app()->bound('events')) {
            $dispatcher = app('events');
            $name = static::class;

            $dispatcher->listen('action.' . $event . ': ' . $name, $callback);
        }
    }

    final public function withEvents(): static
    {
        $this->dispatcher = app('events');

        return $this;
    }

    final public function withoutEvents(): static
    {
        $this->dispatcher = app(NullDispatcher::class);

        return $this;
    }

    protected function fireActionEvent(string $event, bool $halt = true): mixed
    {
        if (is_null($this->dispatcher)) {
            $this->dispatcher = app()->bound('events') ? app('events') : new NullDispatcher();
        }

        if ($this->dispatcher instanceof NullDispatcher) {
            return null;
        }

        $function = $halt ? 'until' : 'dispatch';

        return $this->dispatcher->{$function}('action.' . $event . ': ' . static::class, $this);
    }
}

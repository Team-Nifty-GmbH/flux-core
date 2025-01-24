<?php

namespace FluxErp\Traits\Action;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\NullDispatcher;
use Illuminate\Events\QueuedClosure;

trait HasActionEvents
{
    public static function booting(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('booting', $callback);
    }

    public static function booted(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('booted', $callback);
    }

    public static function executing(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('executing', $callback);
    }

    public static function executed(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('executed', $callback);
    }

    public static function preparingForValidation(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('preparingForValidation', $callback);
    }

    public static function validating(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('validating', $callback);
    }

    public static function validated(QueuedClosure|callable|array|string $callback): void
    {
        static::registerActionEvent('validated', $callback);
    }

    public static function setEventDispatcher(?Dispatcher $dispatcher = null): void
    {
        static::$dispatcher = $dispatcher;
    }

    final public function withEvents(): static
    {
        static::setEventDispatcher(app('events'));

        return $this;
    }

    final public function withoutEvents(): static
    {
        static::setEventDispatcher(app(NullDispatcher::class));

        return $this;
    }

    protected static function registerActionEvent($event, $callback): void
    {
        if (isset(static::$dispatcher)) {
            $name = static::class;

            static::$dispatcher->listen('action.' . $event . ': ' . $name, $callback);
        }
    }

    protected function fireActionEvent(string $event, bool $halt = true): mixed
    {
        if (is_null(static::$dispatcher)) {
            return null;
        }

        $function = $halt ? 'until' : 'dispatch';

        return static::$dispatcher->{$function}('action.' . $event . ': ' . static::class, $this);
    }
}

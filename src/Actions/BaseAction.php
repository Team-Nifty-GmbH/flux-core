<?php

namespace FluxErp\Actions;

use FluxErp\Traits\Makeable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\NullDispatcher;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Exceptions\UnauthorizedException;

abstract class BaseAction
{
    use Makeable;

    protected array $data;

    protected array $rules = [];

    protected static Dispatcher $dispatcher;

    abstract public static function models(): array;

    abstract public function performAction();

    public function __construct(array $data)
    {
        $this->setEventDispatcher();

        $this->fireActionEvent(event: 'booting', halt: false);

        $this->boot($data);

        $this->fireActionEvent(event: 'booted', halt: false);
    }

    protected function boot(array $data): void
    {
        $this->setData($data[0] ?? [], $data[1] ?? false);
    }

    public function checkPermission(): static
    {
        if (! auth()->user()->can('action.' . static::name())) {
            throw UnauthorizedException::forPermissions(['action.' . static::name()]);
        }

        return $this;
    }

    public static function name(): string
    {
        $exploded = explode('-', Str::kebab(class_basename(static::class)));
        $function = array_shift($exploded);

        return implode('-', $exploded) . '.' . $function;
    }

    public static function description(): ?string
    {
        return Str::of(class_basename(static::class))
            ->headline()
            ->lower()
            ->toString();
    }

    public function setData(array $data, bool $keepEmptyStrings = false): static
    {
        $this->data = $keepEmptyStrings ? $data : $this->convertEmptyStringToNull($data);

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    final public function execute()
    {
        if ($this->fireActionEvent(event: 'executing') === false) {
            return false;
        }

        $returnValue = $this->performAction();

        $this->fireActionEvent(event: 'executed', object: is_object($returnValue) ? $returnValue : null, halt: false);

        return $returnValue;
    }

    final public function validate(): static
    {
        if ($this->fireActionEvent(event: 'validating') !== false) {
            $this->validateData();

            $this->fireActionEvent(event: 'validated', halt: false);
        }

        return $this;
    }

    protected function validateData(): void
    {
        $this->data = Validator::validate($this->data, $this->rules);
    }

    final public function withEvents(): static
    {
        $this->setEventDispatcher();

        return $this;
    }

    final public function withoutEvents(): static
    {
        $this->setEventDispatcher(true);

        return $this;
    }

    protected function convertEmptyStringToNull(array $data): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $this->convertEmptyStringToNull($value); // Recurse into sub-arrays
            }

            return $value === '' ? null : $value;
        }, $data);
    }

    protected function fireActionEvent(string $event, object $object = null, bool $halt = true)
    {
        $function = $halt ? 'until' : 'dispatch';

        return static::$dispatcher->{$function}('action.' . $event . ': ' . static::class, $object ?: $this);
    }

    private function setEventDispatcher(bool $nullDispatcher = false): void
    {
        if (! $nullDispatcher) {
            try {
                static::$dispatcher = app()->make(Dispatcher::class);
            } catch (BindingResolutionException) {
                static::$dispatcher = new NullDispatcher(new \Illuminate\Events\Dispatcher());
            }
        } else {
            static::$dispatcher = new NullDispatcher(static::$dispatcher);
        }
    }
}

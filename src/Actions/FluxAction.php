<?php

namespace FluxErp\Actions;

use FluxErp\Models\Permission;
use FluxErp\Traits\Makeable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Events\NullDispatcher;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\UnauthorizedException;

abstract class FluxAction
{
    use Makeable;

    protected array $data;

    protected array $rules = [];

    protected mixed $result = null;

    protected static Dispatcher $dispatcher;

    protected static bool $hasPermission = true;

    abstract public static function models(): array;

    abstract public function performAction(): mixed;

    public function __construct(array $data)
    {
        $this->setEventDispatcher();

        $this->fireActionEvent(event: 'booting', halt: false);

        static::bootTraits();
        $this->boot($data);

        $this->fireActionEvent(event: 'booted', halt: false);
    }

    protected static function bootTraits(): void
    {
        $class = static::class;
        $booted = [];

        foreach (class_uses_recursive($class) as $trait) {
            $method = 'boot' . class_basename($trait);

            if (method_exists($class, $method) && ! in_array($method, $booted)) {
                forward_static_call([$class, $method]);

                $booted[] = $method;
            }
        }
    }

    protected function boot(array $data): void
    {
        $this->setData($data[0] ?? [], $data[1] ?? false);
    }

    public static function canPerformAction(bool $throwException = true): bool
    {
        try {
            Permission::findByName('action.' . static::name());
        } catch (PermissionDoesNotExist) {
            return true;
        }

        if (! auth()->user()->can('action.' . static::name())) {
            if ($throwException) {
                throw UnauthorizedException::forPermissions(['action.' . static::name()]);
            } else {
                return false;
            }
        }

        return true;
    }

    public function checkPermission(): static
    {
        static::canPerformAction();

        return $this;
    }

    public static function hasPermission(): bool
    {
        return static::$hasPermission;
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

    public static function executed($callback): void
    {
        static::$dispatcher->listen('action.executed: ' . static::class, $callback);
    }

    public function setData(array|Arrayable $data, bool $keepEmptyStrings = false): static
    {
        if (! is_array($data)) {
            $data = $data->toArray();
        }

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

    public function mergeRules(array $rules): static
    {
        $this->rules = array_merge($this->rules, $rules);

        return $this;
    }

    public function setResult(mixed $result): static
    {
        $this->result = $result;

        return $this;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    final public function execute(): mixed
    {
        if ($this->fireActionEvent(event: 'executing') === false) {
            return false;
        }

        $this->result = $this->performAction();

        $this->fireActionEvent(event: 'executed', halt: false);

        return $this->result;
    }

    final public function validate(): static
    {
        $this->fireActionEvent(event: 'preparingForValidation');
        $this->prepareForValidation();

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

    protected function prepareForValidation(): void
    {
        //
    }

    final public function when(callable|bool $condition, callable $callback): static
    {
        if (is_callable($condition)) {
            $condition = $condition();
        }

        if ($condition) {
            $callback($this);
        }

        return $this;
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

    protected function fireActionEvent(string $event, bool $halt = true)
    {
        $function = $halt ? 'until' : 'dispatch';

        return static::$dispatcher->{$function}('action.' . $event . ': ' . static::class, $this);
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

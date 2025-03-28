<?php

namespace FluxErp\Actions;

use FluxErp\Models\Permission;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\Action\HasActionEvents;
use FluxErp\Traits\Action\SupportsApiRequests;
use FluxErp\Traits\Makeable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\UnauthorizedException;

abstract class FluxAction
{
    use Conditionable, HasActionEvents, Makeable, SupportsApiRequests;

    protected static ?Dispatcher $dispatcher;

    protected static bool $hasPermission = true;

    protected ?Authenticatable $actingAs;

    protected array $data;

    protected bool $keepEmptyStrings = false;

    protected mixed $result = null;

    protected array $rules = [];

    abstract public static function models(): array;

    abstract public function performAction(): mixed;

    public function __construct(Arrayable|array $data = [], bool $keepEmptyStrings = false)
    {
        $this->fireActionEvent(event: 'booting', halt: false);

        static::bootTraits();
        $this->keepEmptyStrings = $keepEmptyStrings;
        $data = $data instanceof Arrayable ? $data->toArray() : $data;
        $this->boot($data);

        $this->fireActionEvent(event: 'booted', halt: false);
    }

    public function __serialize(): array
    {
        return [
            'data' => $this->data,
            'rules' => $this->rules,
            'result' => $this->result,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->data = $data['data'];
        $this->rules = $data['rules'];
        $this->result = $data['result'];
    }

    public static function canPerformAction(bool $throwException = true): bool
    {
        if (! static::hasPermission() || (app()->runningInConsole() && ! app()->runningUnitTests())) {
            return true;
        }

        try {
            resolve_static(
                Permission::class,
                'findByName',
                [
                    'name' => 'action.' . static::name(),
                ]
            );
        } catch (PermissionDoesNotExist) {
            return true;
        }

        if (! auth()->user()?->can('action.' . static::name())) {
            if ($throwException) {
                throw UnauthorizedException::forPermissions(['action.' . static::name()]);
            } else {
                return false;
            }
        }

        return true;
    }

    public static function description(): ?string
    {
        return Str::of(class_basename(static::class))
            ->headline()
            ->lower()
            ->toString();
    }

    public static function hasPermission(): bool
    {
        return static::$hasPermission;
    }

    public static function name(): string
    {
        $exploded = explode('_', Str::snake(class_basename(static::class)));
        $function = array_shift($exploded);

        return implode('_', $exploded) . '.' . $function;
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

    /**
     * @return class-string<FluxRuleset>|array<int, class-string<FluxRuleset>>
     */
    protected function getRulesets(): string|array
    {
        return [];
    }

    protected function boot(array $data): void
    {
        $this->setData($data, $this->keepEmptyStrings ?? false);
    }

    public function actingAs(?Authenticatable $user = null): static
    {
        $this->actingAs = $user;

        return $this;
    }

    public function addRules(array $rules): static
    {
        foreach ($rules as $key => $value) {
            data_set($this->rules, $key, array_merge(
                Arr::wrap(data_get($this->rules, $key, [])),
                Arr::wrap($value)
            ));
        }

        return $this;
    }

    public function checkPermission(): static
    {
        static::canPerformAction();

        return $this;
    }

    final public function execute(): mixed
    {
        if ($this->fireActionEvent(event: 'executing') === false) {
            return false;
        }

        $current = null;
        if (
            isset($this->actingAs)
            && (
                (is_null($this->getActingAs()) && ! is_null(auth()->user()))
                || (is_null(auth()->user()) && ! is_null($this->getActingAs()))
                || $this->getActingAs()?->isNot(auth()->user())
            )
        ) {
            $current = auth()->user();
            if ($this->getActingAs()) {
                auth()->setUser($this->getActingAs());
            } elseif (method_exists(auth(), 'logout')) {
                auth()->logout();
            }
        }

        DB::transaction(function (): void {
            $this->result = $this->performAction();
        });

        if ($current) {
            if (method_exists(auth(), 'login')) {
                auth()->login($current);
            } else {
                auth()->setUser($current);
            }
        } elseif (isset($this->actingAs)) {
            if (method_exists(auth(), 'logout')) {
                auth()->logout();
            } else {
                auth()->forgetUser();
            }
        }

        $this->fireActionEvent(event: 'executed', halt: false);

        return $this->result;
    }

    public function getActingAs(): ?Authenticatable
    {
        return $this->actingAs;
    }

    public function getData(?string $key = null, mixed $default = null): mixed
    {
        return $key ? data_get($this->data, $key, $default) : $this->data;
    }

    public function getResult(): mixed
    {
        return $this->result;
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

    public function setData(array|Arrayable $data, bool $keepEmptyStrings = false): static
    {
        if (! is_array($data)) {
            $data = $data->toArray();
        }

        $this->data = $keepEmptyStrings ? $data : $this->convertEmptyStringToNull($data);

        return $this;
    }

    public function setResult(mixed $result): static
    {
        $this->result = $result;

        return $this;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function setRulesFromRulesets(): static
    {
        foreach (Arr::wrap($this->getRulesets()) as $ruleset) {
            $this->mergeRules(resolve_static($ruleset, 'getRules'));
        }

        return $this;
    }

    final public function validate(): static
    {
        if (! $this->rules) {
            $this->setRulesFromRulesets();
        }

        $this->fireActionEvent(event: 'preparingForValidation');
        $this->prepareForValidation();

        if ($this->fireActionEvent(event: 'validating') !== false) {
            $this->validateData();

            $this->fireActionEvent(event: 'validated', halt: false);
        }

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

    protected function prepareForValidation(): void
    {
        //
    }

    protected function validateData(): void
    {
        $this->data = Validator::validate($this->getData(), $this->getRules());
    }
}

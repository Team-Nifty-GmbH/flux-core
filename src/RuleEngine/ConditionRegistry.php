<?php

namespace FluxErp\RuleEngine;

use FluxErp\RuleEngine\Contracts\ConditionInterface;
use InvalidArgumentException;

class ConditionRegistry
{
    /** @var array<string, class-string<ConditionInterface>> */
    private array $conditions = [];

    /** @param array<class-string<ConditionInterface>>|class-string<ConditionInterface> $conditions */
    public function register(array|string $conditions): void
    {
        $conditions = is_array($conditions) ? $conditions : [$conditions];

        foreach ($conditions as $conditionClass) {
            if (! is_a($conditionClass, ConditionInterface::class, true)) {
                throw new InvalidArgumentException(
                    sprintf('%s must implement %s', $conditionClass, ConditionInterface::class)
                );
            }

            $this->conditions[$conditionClass::type()] = $conditionClass;
        }
    }

    public function resolve(string $type): ?ConditionInterface
    {
        $class = $this->conditions[$type] ?? null;

        return $class ? app($class) : null;
    }

    /** @return array<string, class-string<ConditionInterface>> */
    public function all(): array
    {
        return $this->conditions;
    }

    /** @return array<string, array<string, class-string<ConditionInterface>>> */
    public function grouped(): array
    {
        $grouped = [];

        foreach ($this->conditions as $type => $class) {
            if (in_array($type, ['or_container', 'and_container'])) {
                continue;
            }

            $grouped[$class::group()][$type] = $class;
        }

        return $grouped;
    }

    public function has(string $type): bool
    {
        return isset($this->conditions[$type]);
    }
}

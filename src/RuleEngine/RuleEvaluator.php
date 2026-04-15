<?php

namespace FluxErp\RuleEngine;

use FluxErp\Models\Rule;
use FluxErp\Models\RuleCondition;
use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\Scopes\RuleScope;

class RuleEvaluator
{
    public static function evaluate(Rule $rule, RuleScope $scope): bool
    {
        if (! $rule->is_active) {
            return false;
        }

        $rootConditions = $rule->rootConditions()->with('children.children')->get();

        if ($rootConditions->isEmpty()) {
            return true;
        }

        foreach ($rootConditions as $rootCondition) {
            if (static::evaluateCondition($rootCondition, $scope)) {
                return true;
            }
        }

        return false;
    }

    protected static function evaluateCondition(RuleCondition $condition, RuleScope $scope): bool
    {
        return match ($condition->type) {
            'or_container' => static::evaluateOrContainer($condition, $scope),
            'and_container' => static::evaluateAndContainer($condition, $scope),
            default => static::evaluateLeaf($condition, $scope),
        };
    }

    protected static function evaluateOrContainer(RuleCondition $condition, RuleScope $scope): bool
    {
        $children = $condition->children;

        if ($children->isEmpty()) {
            return true;
        }

        foreach ($children as $child) {
            if (static::evaluateCondition($child, $scope)) {
                return true;
            }
        }

        return false;
    }

    protected static function evaluateAndContainer(RuleCondition $condition, RuleScope $scope): bool
    {
        $children = $condition->children;

        if ($children->isEmpty()) {
            return true;
        }

        foreach ($children as $child) {
            if (! static::evaluateCondition($child, $scope)) {
                return false;
            }
        }

        return true;
    }

    protected static function evaluateLeaf(RuleCondition $condition, RuleScope $scope): bool
    {
        $registry = app(ConditionRegistry::class);
        $conditionInstance = $registry->resolve($condition->type);

        if (! $conditionInstance instanceof ConditionInterface) {
            return false;
        }

        $supportedScopes = $conditionInstance::supportedScopes();
        if (! empty($supportedScopes) && ! in_array(get_class($scope), $supportedScopes)) {
            return true; // Unsupported scope = not applicable = pass
        }

        if (is_array($condition->value)) {
            foreach ($condition->value as $key => $val) {
                if (property_exists($conditionInstance, $key)) {
                    $conditionInstance->{$key} = $val;
                }
            }
        }

        return $conditionInstance->match($scope);
    }
}

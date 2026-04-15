<?php

namespace FluxErp\Livewire;

use FluxErp\Actions\RuleCondition\CreateRuleCondition;
use FluxErp\Actions\RuleCondition\DeleteRuleCondition;
use FluxErp\Actions\RuleCondition\UpdateRuleCondition;
use FluxErp\Models\Rule;
use FluxErp\Models\RuleCondition;
use FluxErp\RuleEngine\ConditionRegistry;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RuleConditionBuilder extends Component
{
    use Actions;

    #[Locked]
    public int $ruleId;

    public array $conditionTree = [];

    public function mount(int $ruleId): void
    {
        $this->ruleId = $ruleId;
        $this->loadConditions();
    }

    public function render()
    {
        return view('flux::livewire.rule-condition-builder', [
            'conditionTypes' => app(ConditionRegistry::class)->grouped(),
        ]);
    }

    public function loadConditions(): void
    {
        $rule = Rule::query()->with('conditions.children.children')->find($this->ruleId);
        $this->conditionTree = $this->buildTree($rule?->rootConditions ?? collect());
    }

    #[Renderless]
    public function addOrGroup(): void
    {
        $orContainer = $this->getOrCreateRootContainer();

        try {
            CreateRuleCondition::make([
                'rule_id' => $this->ruleId,
                'parent_id' => $orContainer->getKey(),
                'type' => 'and_container',
                'position' => RuleCondition::query()
                    ->where('parent_id', $orContainer->getKey())
                    ->count(),
            ])->validate()->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadConditions();
    }

    #[Renderless]
    public function addCondition(int $andContainerId, string $type): void
    {
        $registry = app(ConditionRegistry::class);

        if (! $registry->has($type)) {
            return;
        }

        try {
            CreateRuleCondition::make([
                'rule_id' => $this->ruleId,
                'parent_id' => $andContainerId,
                'type' => $type,
                'value' => [],
                'position' => RuleCondition::query()
                    ->where('parent_id', $andContainerId)
                    ->count(),
            ])->validate()->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadConditions();
    }

    #[Renderless]
    public function updateConditionValue(int $conditionId, array $value): void
    {
        try {
            UpdateRuleCondition::make([
                'id' => $conditionId,
                'value' => $value,
            ])->validate()->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }
    }

    #[Renderless]
    public function removeCondition(int $conditionId): void
    {
        try {
            DeleteRuleCondition::make(['id' => $conditionId])
                ->validate()->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadConditions();
    }

    #[Renderless]
    public function removeOrGroup(int $andContainerId): void
    {
        try {
            DeleteRuleCondition::make(['id' => $andContainerId])
                ->validate()->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadConditions();
    }

    protected function getOrCreateRootContainer(): RuleCondition
    {
        $root = RuleCondition::query()
            ->where('rule_id', $this->ruleId)
            ->whereNull('parent_id')
            ->where('type', 'or_container')
            ->first();

        if (! $root) {
            $root = CreateRuleCondition::make([
                'rule_id' => $this->ruleId,
                'type' => 'or_container',
                'position' => 0,
            ])->validate()->execute();
        }

        return $root;
    }

    protected function buildTree($conditions): array
    {
        return $conditions->map(function (RuleCondition $condition) {
            return [
                'id' => $condition->getKey(),
                'type' => $condition->type,
                'value' => $condition->value ?? [],
                'children' => $this->buildTree($condition->children),
            ];
        })->toArray();
    }
}

<?php

namespace FluxErp\Support\ProductConfiguration;

use FluxErp\Enums\ConfigurationRuleTypeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Models\Product;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Models\ProductConfigurationRule;
use FluxErp\Traits\Makeable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ConfigurationValidator
{
    use Makeable;

    protected ?Collection $groups = null;

    public function __construct(protected Product $product) {}

    public function selection(array $configuration): Collection
    {
        $items = resolve_static(ProductConfigurationGroupItem::class, 'query')
            ->whereKey(array_column($configuration, 'product_configuration_group_item_id'))
            ->with([
                'product' => fn (Relation $query) => $query->withTrashed(),
                'productConfigurationGroup' => fn (Relation $query) => $query->withTrashed(),
                'productConfigurationRules.targetGroup' => fn (Relation $query) => $query->withTrashed(),
                'productConfigurationRules.targetItems.product:id,name',
            ])
            ->get()
            ->keyBy(fn (ProductConfigurationGroupItem $item) => $item->getKey());

        return collect($configuration)
            ->map(fn (array $entry) => [
                'item' => $items->get(data_get($entry, 'product_configuration_group_item_id')),
                'amount' => (int) data_get($entry, 'amount'),
            ])
            ->values();
    }

    public function unsatisfiableRules(): array
    {
        $groups = $this->groups();

        return resolve_static(ProductConfigurationRule::class, 'query')
            ->where('rule_type_enum', ConfigurationRuleTypeEnum::Requires->value)
            ->whereHas(
                'productConfigurationGroupItem',
                fn (Builder $query) => $query->whereIntegerInRaw('product_configuration_group_id', $groups->keys()->all())
            )
            ->with([
                'productConfigurationGroupItem.product:id,name',
                'targetItems:id,product_configuration_group_id',
            ])
            ->get()
            ->filter(fn (ProductConfigurationRule $rule) => $rule->target_group_id
                ? ! $groups->has($rule->target_group_id)
                : $rule->targetItems->doesntContain(
                    fn (ProductConfigurationGroupItem $target) => $groups->has($target->product_configuration_group_id)
                ))
            ->map(fn (ProductConfigurationRule $rule) => __(
                ':item requires an option from a group that is not attached to this product.',
                ['item' => $rule->productConfigurationGroupItem->product?->name]
            ))
            ->values()
            ->all();
    }

    public function validate(array $configuration, bool $isDefault = false): array
    {
        $selection = $this->selection($configuration);
        $groups = $this->groups();
        $messages = [];

        $itemIds = array_column($configuration, 'product_configuration_group_item_id');
        if (count($itemIds) !== count(array_unique($itemIds))) {
            $messages[] = __('An option was selected more than once.');
        }

        foreach ($selection as $entry) {
            $item = $entry['item'];

            if (! $item) {
                $messages[] = __('The selected option no longer exists.');

                continue;
            }

            $name = $item->product?->name;

            if ($item->productConfigurationGroup->trashed()) {
                $messages[] = __(':group has been deleted.', ['group' => $item->productConfigurationGroup->name]);
            } elseif (! $item->product || $item->product->trashed()) {
                $messages[] = __(':item is no longer available.', ['item' => $name]);
            } elseif (! $groups->has($item->product_configuration_group_id)) {
                $messages[] = __(':item does not belong to this product.', ['item' => $name]);
            }

            if ($entry['amount'] < 1 || $entry['amount'] > $item->max_amount) {
                $messages[] = __(':item needs an amount between 1 and :max.', [
                    'item' => $name,
                    'max' => $item->max_amount,
                ]);
            }
        }

        $this->throwIfAny($messages);

        $selectedByGroup = $selection->groupBy(fn (array $entry) => $entry['item']->product_configuration_group_id);
        $selectedIds = $selection->map(fn (array $entry) => $entry['item']->getKey());

        foreach ($groups as $group) {
            $messages = array_merge(
                $messages,
                $this->groupViolations(
                    $group,
                    $selectedByGroup->get($group->getKey(), collect())->count(),
                    $isDefault
                )
            );
        }

        foreach ($selection as $entry) {
            foreach ($entry['item']->productConfigurationRules as $rule) {
                if ($message = $this->ruleViolation($entry['item'], $rule, $selectedByGroup, $selectedIds)) {
                    $messages[] = $message;
                }
            }
        }

        $this->throwIfAny($messages);

        return $selection
            ->map(fn (array $entry) => [
                'product_configuration_group_item_id' => $entry['item']->getKey(),
                'amount' => $entry['amount'],
            ])
            ->all();
    }

    protected function groupViolations(ProductConfigurationGroup $group, int $count, bool $isDefault): array
    {
        $messages = [];
        $isRequired = (bool) $group->pivot->is_required;

        if ($group->selection_type_enum === ConfigurationSelectionTypeEnum::Single && $count > 1) {
            $messages[] = __(':group allows only one selection.', ['group' => $group->name]);
        }

        if ($group->selection_type_enum === ConfigurationSelectionTypeEnum::Multiple) {
            if (! $isDefault
                && ($count > 0 || $isRequired)
                && ! is_null($group->min_selections)
                && $count < $group->min_selections
            ) {
                $messages[] = __(':group needs at least :min selections.', [
                    'group' => $group->name,
                    'min' => $group->min_selections,
                ]);
            }

            if (! is_null($group->max_selections) && $count > $group->max_selections) {
                $messages[] = __(':group allows at most :max selections.', [
                    'group' => $group->name,
                    'max' => $group->max_selections,
                ]);
            }
        }

        if (! $isDefault && $isRequired && $count === 0) {
            $messages[] = __(':group requires a selection.', ['group' => $group->name]);
        }

        return $messages;
    }

    protected function groups(): Collection
    {
        return $this->groups ??= $this->product
            ->productConfigurationGroups()
            ->get()
            ->keyBy(fn (ProductConfigurationGroup $group) => $group->getKey());
    }

    protected function ruleViolation(
        ProductConfigurationGroupItem $item,
        ProductConfigurationRule $rule,
        Collection $selectedByGroup,
        Collection $selectedIds
    ): ?string {
        $requires = $rule->rule_type_enum === ConfigurationRuleTypeEnum::Requires;
        $name = $item->product?->name;

        if ($rule->target_group_id) {
            $hasSelection = $selectedByGroup->get($rule->target_group_id, collect())->isNotEmpty();
            $replace = ['item' => $name, 'group' => $rule->targetGroup?->name];

            return match (true) {
                $requires && ! $hasSelection => __(':item requires a selection in :group.', $replace),
                ! $requires && $hasSelection => __(':item cannot be combined with a selection in :group.', $replace),
                default => null,
            };
        }

        $hasSelection = $rule->targetItems->contains(
            fn (ProductConfigurationGroupItem $target) => $selectedIds->contains($target->getKey())
        );
        $replace = [
            'item' => $name,
            'items' => $rule->targetItems
                ->map(fn (ProductConfigurationGroupItem $target) => $target->product?->name)
                ->implode(', '),
        ];

        return match (true) {
            $requires && ! $hasSelection => __(':item requires one of :items.', $replace),
            ! $requires && $hasSelection => __(':item cannot be combined with :items.', $replace),
            default => null,
        };
    }

    protected function throwIfAny(array $messages): void
    {
        if ($messages) {
            throw ValidationException::withMessages(['configuration' => array_values(array_unique($messages))]);
        }
    }
}

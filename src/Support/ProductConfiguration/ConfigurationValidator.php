<?php

namespace FluxErp\Support\ProductConfiguration;

use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Models\Product;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Traits\Makeable;
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

    public function validate(array $configuration): array
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

        foreach ($groups as $group) {
            $messages = array_merge(
                $messages,
                $this->groupViolations($group, $selectedByGroup->get($group->getKey(), collect())->count())
            );
        }

        $this->throwIfAny($messages);

        return $selection
            ->map(fn (array $entry) => [
                'product_configuration_group_item_id' => $entry['item']->getKey(),
                'amount' => $entry['amount'],
            ])
            ->all();
    }

    protected function groupViolations(ProductConfigurationGroup $group, int $count): array
    {
        $messages = [];
        $isRequired = (bool) $group->pivot->is_required;

        if ($group->selection_type_enum === ConfigurationSelectionTypeEnum::Single && $count > 1) {
            $messages[] = __(':group allows only one selection.', ['group' => $group->name]);
        }

        if ($group->selection_type_enum === ConfigurationSelectionTypeEnum::Multiple) {
            if (($count > 0 || $isRequired)
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

        if ($isRequired && $count === 0) {
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

    protected function throwIfAny(array $messages): void
    {
        if ($messages) {
            throw ValidationException::withMessages(['configuration' => array_values(array_unique($messages))]);
        }
    }
}

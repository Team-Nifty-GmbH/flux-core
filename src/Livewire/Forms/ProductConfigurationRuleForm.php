<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\ProductConfigurationRule\CreateProductConfigurationRule;
use FluxErp\Actions\ProductConfigurationRule\DeleteProductConfigurationRule;
use FluxErp\Actions\ProductConfigurationRule\UpdateProductConfigurationRule;
use Livewire\Attributes\Locked;

class ProductConfigurationRuleForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $product_configuration_group_item_id = null;

    public ?string $rule_type_enum = 'requires';

    public ?int $target_group_id = null;

    public array $target_items = [];

    public function toActionData(): array
    {
        $data = parent::toActionData();

        if ($this->target_group_id) {
            unset($data['target_items']);
        } else {
            $data['target_group_id'] = null;
        }

        return $data;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateProductConfigurationRule::class,
            'update' => UpdateProductConfigurationRule::class,
            'delete' => DeleteProductConfigurationRule::class,
        ];
    }
}

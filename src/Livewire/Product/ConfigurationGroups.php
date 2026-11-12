<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Actions\Product\ProductProductConfigurationGroup\CreateProductProductConfigurationGroup;
use FluxErp\Actions\Product\ProductProductConfigurationGroup\DeleteProductProductConfigurationGroup;
use FluxErp\Actions\Product\ProductProductConfigurationGroup\UpdateProductProductConfigurationGroup;
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Models\Product;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class ConfigurationGroups extends Component
{
    use Actions;

    public array $assignments = [];

    public ?int $groupId = null;

    #[Modelable]
    public ProductForm $product;

    public function mount(): void
    {
        $this->loadAssignments();
    }

    public function render(): View
    {
        return view('flux::livewire.product.configuration-groups', [
            'groups' => resolve_static(ProductConfigurationGroup::class, 'query')
                ->whereKeyNot(array_column($this->assignments, 'id'))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray(),
        ]);
    }

    public function attach(): bool
    {
        try {
            CreateProductProductConfigurationGroup::make([
                'product_id' => $this->product->id,
                'product_configuration_group_id' => $this->groupId,
                'sort' => count($this->assignments),
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->groupId = null;
        $this->loadAssignments();

        return true;
    }

    public function detach(int $pivotId): bool
    {
        try {
            DeleteProductProductConfigurationGroup::make(['pivot_id' => $pivotId])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadAssignments();

        return true;
    }

    public function save(): bool
    {
        try {
            foreach ($this->assignments as $assignment) {
                UpdateProductProductConfigurationGroup::make([
                    'pivot_id' => $assignment['pivot_id'],
                    'is_required' => (bool) $assignment['is_required'],
                    'sort' => (int) $assignment['sort'],
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            }
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadAssignments();

        return true;
    }

    protected function loadAssignments(): void
    {
        $product = resolve_static(Product::class, 'query')
            ->whereKey($this->product->id)
            ->with('productConfigurationGroups')
            ->first();

        $this->assignments = $product->productConfigurationGroups
            ->map(fn (ProductConfigurationGroup $group) => [
                'id' => $group->getKey(),
                'pivot_id' => $group->pivot->pivot_id,
                'name' => $group->name,
                'is_required' => (bool) $group->pivot->is_required,
                'sort' => $group->pivot->sort,
            ])
            ->all();
    }
}

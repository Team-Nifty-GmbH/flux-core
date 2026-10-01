<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Actions\Project\UpdateProject;
use FluxErp\Livewire\DataTables\OrderList;
use FluxErp\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Orders extends OrderList
{
    #[Locked]
    public ?int $contactId = null;

    public ?string $includeBefore = 'flux::livewire.project.orders';

    #[Locked]
    public ?int $mainOrderId = null;

    #[Modelable]
    public int $projectId;

    public ?int $supplementaryOrderId = null;

    public function mount(): void
    {
        parent::mount();

        $project = resolve_static(Project::class, 'query')
            ->whereKey($this->projectId)
            ->first(['id', 'contact_id', 'order_id']);

        $this->contactId = $project?->contact_id;
        $this->mainOrderId = $project?->order_id;
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Add Supplementary Order'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(UpdateProject::class, 'canPerformAction', [false]))
                ->attributes([
                    'x-on:click' => '$tsui.open.modal(\'add-supplementary-order-modal\')',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Remove'))
                ->icon('trash')
                ->color('red')
                ->when(resolve_static(UpdateProject::class, 'canPerformAction', [false]))
                ->attributes([
                    'x-show' => 'record.id !== $wire.mainOrderId',
                    'x-cloak' => true,
                    'wire:flux-confirm.type.error' => __('Remove this supplementary order from the project?'),
                    'wire:click' => 'removeSupplementaryOrder(record.id)',
                ]),
        ];
    }

    #[Renderless]
    public function addSupplementaryOrder(): bool
    {
        if (! $this->supplementaryOrderId) {
            return false;
        }

        $saved = $this->syncSupplementaryOrders(
            array_merge($this->supplementaryOrderIds(), [$this->supplementaryOrderId])
        );

        if ($saved) {
            $this->supplementaryOrderId = null;
            $this->modalClose('add-supplementary-order-modal');
        }

        return $saved;
    }

    #[Renderless]
    public function getCacheKey(): string
    {
        return parent::getCacheKey() . $this->projectId;
    }

    #[Renderless]
    public function removeSupplementaryOrder(int $orderId): bool
    {
        return $this->syncSupplementaryOrders(
            array_values(array_diff($this->supplementaryOrderIds(), [$orderId]))
        );
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where(fn (Builder $query) => $query
            ->whereKey($this->mainOrderId)
            ->orWhereRelation('supplementedProjects', 'projects.id', $this->projectId)
        );
    }

    protected function supplementaryOrderIds(): array
    {
        return resolve_static(Project::class, 'query')
            ->whereKey($this->projectId)
            ->first()
            ?->supplementaryOrders()
            ->pluck('orders.id')
            ->all() ?? [];
    }

    protected function syncSupplementaryOrders(array $orderIds): bool
    {
        try {
            UpdateProject::make([
                'id' => $this->projectId,
                'supplementary_orders' => $orderIds,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}

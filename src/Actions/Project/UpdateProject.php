<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Models\Project;
use FluxErp\Rulesets\Project\UpdateProjectRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateProject extends FluxAction
{
    public static function models(): array
    {
        return [Project::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateProjectRuleset::class;
    }

    public function performAction(): Model
    {
        $supplementaryOrders = Arr::pull($this->data, 'supplementary_orders');

        $project = resolve_static(Project::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $project->fill($this->data);
        $project->save();

        if (! is_null($supplementaryOrders)) {
            $project->supplementaryOrders()->sync($supplementaryOrders);
        }

        if ($project->order_id) {
            $project->supplementaryOrders()->detach($project->order_id);
        }

        return $project->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $hasSupplementaryOrders = array_key_exists('supplementary_orders', $this->data);

        if (! $hasSupplementaryOrders && ! array_key_exists('contact_id', $this->data)) {
            return;
        }

        $project = resolve_static(Project::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['id', 'contact_id', 'order_id']);

        $mainOrderId = $this->toNullableInt(
            array_key_exists('order_id', $this->data) ? $this->getData('order_id') : $project->order_id
        );
        $contactId = $this->toNullableInt(
            array_key_exists('contact_id', $this->data) ? $this->getData('contact_id') : $project->contact_id
        );

        $orders = resolve_static(Order::class, 'query')
            ->whereIntegerInRaw(
                'id',
                $hasSupplementaryOrders
                    ? array_map('intval', $this->getData('supplementary_orders') ?? [])
                    : $project->supplementaryOrders()->pluck('orders.id')->all()
            )
            ->with('orderType:id,order_type_enum')
            ->get(['id', 'contact_id', 'order_type_id']);

        if ($orders->isEmpty()) {
            return;
        }

        if (! $hasSupplementaryOrders) {
            if ($orders->contains(fn (Order $order): bool => $order->contact_id !== $contactId)) {
                throw ValidationException::withMessages([
                    'contact_id' => [
                        __('The project has supplementary orders of another contact. Remove them before changing the contact.'),
                    ],
                ])
                    ->errorBag('updateProject');
            }

            return;
        }

        $errors = [];

        if ($orders->contains(fn (Order $order): bool => $order->getKey() === $mainOrderId)) {
            $errors[] = __('The main order of the project cannot be a supplementary order.');
        }

        if ($orders->contains(fn (Order $order): bool => (bool) $order->orderType?->order_type_enum?->isPurchase())) {
            $errors[] = __('A purchase order cannot be a supplementary order.');
        }

        if ($orders->contains(fn (Order $order): bool => $order->contact_id !== $contactId)) {
            $errors[] = __('A supplementary order must belong to the contact of the project.');
        }

        if ($errors) {
            throw ValidationException::withMessages(['supplementary_orders' => $errors])
                ->errorBag('updateProject');
        }
    }

    protected function toNullableInt(mixed $value): ?int
    {
        return is_null($value) ? null : (int) $value;
    }
}

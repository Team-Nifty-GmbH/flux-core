<?php

namespace FluxErp\Actions\Commission;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Commission;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\Tenant;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use FluxErp\Rulesets\Commission\CreateCommissionCreditNotesRuleset;
use FluxErp\Support\Collection\OrderCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class CreateCommissionCreditNotes extends DispatchableFluxAction
{
    protected array $agents = [];

    protected ?int $vatRateId = null;

    public static function models(): array
    {
        return [Commission::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateCommissionCreditNotesRuleset::class;
    }

    public function performAction(): OrderCollection
    {
        $orderIds = [];
        foreach ($this->agents as $agentId => $agentData) {
            foreach (data_get($agentData, 'tenant_ids') as $tenantId) {
                $order = CreateOrder::make([
                    'order_type_id' => resolve_static(Tenant::class, 'query')
                        ->whereKey($tenantId)
                        ->value('commission_credit_note_order_type_id')
                        ?? resolve_static(OrderType::class, 'query')
                            ->where('tenant_id', $tenantId)
                            ->where('order_type_enum', OrderTypeEnum::Refund)
                            ->value('id'),
                    'tenant_id' => $tenantId,
                    'contact_id' => data_get($agentData, 'contact_id'),
                ])
                    ->validate()
                    ->execute();
                $orderIds[] = $order->id;

                $commissions = resolve_static(Commission::class, 'query')
                    ->where('user_id', $agentId)
                    ->whereIntegerInRaw('id', $this->data)
                    ->whereDoesntHave('creditNoteOrderPosition')
                    ->withWhereHas(
                        'orderPosition',
                        fn (Builder|BelongsTo $query) => $query->where('tenant_id', $tenantId)
                    )
                    ->withWhereHas(
                        'order',
                        fn (Builder|BelongsTo $query) => $query
                            ->with([
                                'addressInvoice:id,name,country_id',
                                'addressInvoice.country:id,iso_alpha2',
                            ])
                    )
                    ->with([
                        'user:id,contact_id',
                        'user.contact:id,invoice_address_id',
                        'user.contact.country:iso_alpha2',
                        'user.contact.invoiceAddress:id,contact_id,country_id',
                        'user.contact.invoiceAddress.country:id,iso_alpha2',
                    ])
                    ->get();

                foreach ($commissions as $commission) {
                    $orderPosition = CreateOrderPosition::make([
                        'order_id' => $order->id,
                        'vat_rate_id' => $this->vatRateId,
                        'unit_price' => $commission->commission,
                        'quantity' => 1,
                        'is_net' => true,
                        'name' => $commission->getLabel(),
                        'description' => $commission->getDescription(),
                    ])
                        ->validate()
                        ->execute();

                    resolve_static(Commission::class, 'query')
                        ->whereKey($commission->id)
                        ->update(['credit_note_order_position_id' => $orderPosition->id]);
                }

                $order->calculatePrices()->save();
            }
        }

        return resolve_static(Order::class, 'query')
            ->whereIntegerInRaw('id', $orderIds)
            ->get();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $agents = resolve_static(User::class, 'query')
            ->whereHas('commissions', fn (Builder $query) => $query->whereIntegerInRaw('id', $this->data))
            ->get(['id', 'name', 'contact_id']);

        $tenantIds = [];
        foreach ($agents as $agent) {
            $this->agents[$agent->id] = [
                'contact_id' => $agent->contact_id,
                'tenant_ids' => resolve_static(OrderPosition::class, 'query')
                    ->whereHas('commission', fn (Builder $query) => $query->whereIntegerInRaw('id', $this->data)
                        ->where('user_id', $agent->id)
                    )
                    ->distinct('tenant_id')
                    ->pluck('tenant_id')
                    ->toArray(),
            ];

            $tenantIds = array_merge($tenantIds, $this->agents[$agent->id]['tenant_ids']);

            if (! $agent->contact) {
                $errors += [
                    'agent' => [__('No contact found for agent :agent', ['agent' => $agent->name])],
                ];
            }
        }

        foreach (
            resolve_static(Tenant::class, 'query')
                ->whereIntegerInRaw('id', $tenantIds)
                ->get(['id', 'name']) as $tenant
        ) {
            if (! resolve_static(OrderType::class, 'query')
                ->where('tenant_id', $tenant->id)
                ->where('order_type_enum', OrderTypeEnum::Refund)
                ->value('id')
            ) {
                $errors += [
                    'order_type_id' => [
                        __('No refund order type found for tenant :tenant', ['tenant' => $tenant->name]),
                    ],
                ];
            }
        }

        $this->vatRateId = $this->getData('vat_rate_id')
            ?? resolve_static(VatRate::class, 'default')?->getKey();

        if (! $this->vatRateId) {
            $errors += [
                'vat_rate_id' => ['No default VAT rate found'],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('createCommissionCreditNote');
        }
    }
}

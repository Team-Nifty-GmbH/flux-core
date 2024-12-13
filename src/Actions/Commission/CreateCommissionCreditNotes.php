<?php

namespace FluxErp\Actions\Commission;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\Commission;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
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

    protected function getRulesets(): string|array
    {
        return CreateCommissionCreditNotesRuleset::class;
    }

    public static function models(): array
    {
        return [Commission::class];
    }

    public function performAction(): OrderCollection
    {
        $orderIds = [];
        foreach ($this->agents as $agentId => $agentData) {
            foreach (data_get($agentData, 'client_ids') as $clientId) {
                $order = CreateOrder::make([
                    'order_type_id' => resolve_static(Client::class, 'query')
                        ->whereKey($clientId)
                        ->value('commission_credit_note_order_type_id')
                        ?? resolve_static(OrderType::class, 'query')
                            ->where('client_id', $clientId)
                            ->where('order_type_enum', OrderTypeEnum::Refund)
                            ->value('id'),
                    'client_id' => $clientId,
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
                        fn (Builder|BelongsTo $query) => $query->where('client_id', $clientId)
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

        $clientIds = [];
        foreach ($agents as $agent) {
            $this->agents[$agent->id] = [
                'contact_id' => $agent->contact_id,
                'client_ids' => resolve_static(OrderPosition::class, 'query')
                    ->whereHas('commission', fn (Builder $query) => $query->whereIntegerInRaw('id', $this->data)
                        ->where('user_id', $agent->id)
                    )
                    ->distinct('client_id')
                    ->pluck('client_id')
                    ->toArray(),
            ];

            $clientIds = array_merge($clientIds, $this->agents[$agent->id]['client_ids']);

            if (! $agent->contact) {
                $errors += [
                    'agent' => [__('No contact found for agent :agent', ['agent' => $agent->name])],
                ];
            }
        }

        foreach (
            resolve_static(Client::class, 'query')
                ->whereIntegerInRaw('id', $clientIds)
                ->get(['id', 'name']) as $client
        ) {
            if (! resolve_static(OrderType::class, 'query')
                ->where('client_id', $client->id)
                ->where('order_type_enum', OrderTypeEnum::Refund)
                ->value('id')
            ) {
                $errors += [
                    'order_type_id' => [
                        __('No refund order type found for client :client', ['client' => $client->name]),
                    ],
                ];
            }
        }

        $this->vatRateId = $this->getData('vat_rate_id') ?? VatRate::default()?->id;

        if (! $this->vatRateId) {
            $errors += [
                'vat_rate_id' => [__('No default VAT rate found')],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('createCommissionCreditNote');
        }
    }
}

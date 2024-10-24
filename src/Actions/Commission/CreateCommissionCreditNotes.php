<?php

namespace FluxErp\Actions\Commission;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Enums\OrderTypeEnum;
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
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreateCommissionCreditNotes extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);

        $this->rules = resolve_static(CreateCommissionCreditNotesRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Commission::class];
    }

    public function performAction(): OrderCollection
    {
        $agents = User::query()
            ->withWhereHas('contact', fn (Builder|BelongsTo $query) => $query->with('invoiceAddress.country'))
            ->whereHas('commissions', fn (Builder|HasMany $query) => $query->whereIntegerInRaw('id', $this->data))
            ->get(['id', 'name', 'email', 'contact_id']);
        $orderIds = [];

        foreach ($agents as $agent) {
            $clientIds = resolve_static(OrderPosition::class, 'query')
                ->whereHas('commission', fn (Builder $query) => $query->whereIntegerInRaw('id', $this->data)
                    ->where('user_id', $agent->id)
                )
                ->distinct('client_id')
                ->get('client_id')
                ->pluck('client_id');

            foreach ($clientIds as $clientId) {
                $commissions = $agent->commissions()
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
                        'user.contact.invoiceAddress:id,contact_id,country_id',
                        'user.contact.invoiceAddress.country:id,iso_alpha2',
                    ])
                    ->get();

                $order = CreateOrder::make([
                    'order_type_id' => resolve_static(OrderType::class, 'query')
                        ->where('order_type_enum', OrderTypeEnum::Refund)
                        ->value('id'),
                    'client_id' => $clientId,
                    'contact_id' => $agent->contact_id,
                ])
                    ->validate()
                    ->execute();
                $orderIds[] = $order->id;

                foreach ($commissions as $commission) {
                    $orderPosition = CreateOrderPosition::make([
                        'order_id' => $order->id,
                        'vat_rate_id' => VatRate::default()->id,
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
}

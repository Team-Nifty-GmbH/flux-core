<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Commission extends FluxModel implements InteractsWithDataTables
{
    use HasPackageFactory, HasUserModification, HasUuid;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'array',
        ];
    }

    public function creditNoteOrderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class, 'credit_note_order_position_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLabel(): ?string
    {
        return $this->order->addressInvoice->name . ' (' .
            $this->order->invoice_number . ' ' .
            $this->order->invoice_date
                ->locale($this->user->contact->country?->iso_alpha2 ?? Country::default()->iso_alpha2)
                ->isoFormat('L') . ')';
    }

    public function getDescription(): ?string
    {
        return $this->orderPosition->name . ' ' .
            Number::currency(
                number: $this->orderPosition->total_net_price,
                in: Currency::default()->iso,
                locale: $this->user->contact->country?->iso_alpha2 ?? Country::default()->iso_alpha2
            ) . ' - ' .
            Number::percentage(
                number: bcmul($this->commission_rate['commission_rate'], 100),
                maxPrecision: 2,
                locale: $this->user->contact->country?->iso_alpha2 ?? Country::default()->iso_alpha2
            );
    }

    public function getUrl(): ?string
    {
        return route('accounting.commissions');
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }
}

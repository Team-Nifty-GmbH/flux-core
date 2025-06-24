<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Commission extends FluxModel implements InteractsWithDataTables
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

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

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return ($this->order_position_id ? $this->orderPosition?->name . ' ' : null) .
            Number::currency(
                number: $this->total_net_price,
                in: resolve_static(Currency::class, 'default')?->iso ?? '',
                locale: $this->user->contact?->country?->iso_alpha2
                    ?? resolve_static(Country::class, 'default')?->iso_alpha2
                    ?? app()->getLocale()
            ) . ' - ' .
            Number::percentage(
                number: bcmul(data_get($this->commission_rate, 'commission_rate', 0), 100),
                maxPrecision: 2,
                locale: $this->user->contact?->country?->iso_alpha2
                    ?? resolve_static(Country::class, 'default')?->iso_alpha2
                    ?? app()->getLocale()
            );
    }

    public function getLabel(): ?string
    {
        return $this->order_id
            ? $this->order->addressInvoice->name . ' (' .
                $this->order->invoice_number . ' ' .
                $this->order->invoice_date
                    ->locale(
                        $this->user->contact?->country?->iso_alpha2
                        ?? resolve_static(Country::class, 'default')?->iso_alpha2
                        ?? app()->getLocale()
                    )
                    ->isoFormat('L') . ')'
            : Number::percentage(
                number: bcmul(data_get($this->commission_rate, 'commission_rate', 0), 100),
                maxPrecision: 2,
                locale: $this->user->contact?->country?->iso_alpha2
                ?? resolve_static(Country::class, 'default')?->iso_alpha2
                ?? app()->getLocale()
            ) . ' ' . __('Commission');
    }

    public function getUrl(): ?string
    {
        return route('accounting.commissions');
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
}

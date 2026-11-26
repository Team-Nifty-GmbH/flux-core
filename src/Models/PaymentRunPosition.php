<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Models\Pivots\OrderPaymentRun;
use FluxErp\Support\PaymentRunPositionBuilder;
use FluxErp\Traits\Model\Communicatable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\InteractsWithMedia;
use FluxErp\Traits\Model\Printable;
use FluxErp\View\Printing\PaymentRun\PaymentAdvice;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;

class PaymentRunPosition extends FluxModel implements HasMedia, OffersPrinting
{
    use Communicatable, HasPackageFactory, HasUserModification, HasUuid, InteractsWithMedia, Printable;

    protected function casts(): array
    {
        return [
            'amount' => Money::class,
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function paymentRun(): BelongsTo
    {
        return $this->belongsTo(PaymentRun::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_payment_run', 'payment_run_position_id')
            ->using(OrderPaymentRun::class)
            ->withPivot(['pivot_id', 'payment_run_id', 'amount', 'success']);
    }

    public function refreshFromOrders(): static
    {
        $orders = $this->orders()
            ->get(['orders.id', 'orders.invoice_number']);

        $this->amount = bcsum($orders->pluck('pivot.amount'));
        $this->purpose = app(PaymentRunPositionBuilder::class)->purpose(
            $orders->pluck('invoice_number')->all(),
            $this->end_to_end_id
        );
        $this->save();

        return $this;
    }

    public function resolveMailableAddress(): ?Address
    {
        return filled($this->contact?->invoiceAddress?->email_primary)
            ? $this->contact->invoiceAddress
            : $this->contact?->mainAddress;
    }

    public function getEmailTemplateModelType(): ?string
    {
        return morph_alias(static::class);
    }

    public function getPrintViews(): array
    {
        return [
            'payment-advice' => PaymentAdvice::class,
        ];
    }
}

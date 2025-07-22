<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Actions\PaymentRun\DeletePaymentRun;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Enums\SepaMandateTypeEnum;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\States\PaymentRun\Open;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PaymentRunPreview extends Component
{
    use Actions;

    public array $orders = [];

    #[Locked]
    public ?string $paymentRunTypeEnum = null;

    public function mount(): void
    {
        $orderIds = session()->pull('payment_run_preview_orders', []);
        $this->paymentRunTypeEnum = session()->pull('payment_run_type_enum')?->value;

        if (! $orderIds || ! $this->paymentRunTypeEnum) {
            $this->redirectRoute('accounting.money-transfer', navigate: true);

            return;
        }

        $this->orders = resolve_static(Order::class, 'query')
            ->whereKey($orderIds)
            ->with([
                'contact',
                'addressInvoice',
                'orderType',
                'currency',
                'contactBankConnection.sepaMandates' => fn (HasMany $query) => $query->whereNotNull('signed_date'),
            ])
            ->get()
            ->keyBy('id')
            ->map(fn (Order $order) => [
                'id' => $order->getKey(),
                'invoice_number' => $order->invoice_number,
                'contact_name' => $order->contact?->name,
                'address_name' => $order->addressInvoice?->name,
                'total_gross_price' => $order->total_gross_price,
                'balance' => $order->balance,
                'currency_iso' => $order->currency?->iso
                    ?? resolve_static(Currency::class, 'default')->iso,
                'amount' => bcabs(bcround($order->balance, 2)),
                'multiplier' => bccomp($order->balance, 0),
                'type' => $order->contactBankConnection?->sepaMandates->last()?->sepa_mandate_type_enum ?? null,
            ])
            ->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.accounting.payment-run-preview');
    }

    #[Renderless]
    public function cancel(): void
    {
        $this->redirectRoute('accounting.money-transfer', navigate: true);
    }

    #[Renderless]
    public function createPaymentRun(): void
    {
        $orderPayments = collect($this->orders)
            ->map(fn (array $order, int $orderId) => [
                'order_id' => $orderId,
                'amount' => bcmul(
                    bcabs(bcround(data_get($order, 'amount'), 2)),
                    data_get($order, 'multiplier')
                ),
                'type' => data_get($order, 'type', '__NO_TYPE__'),
            ])
            ->values()
            ->groupBy('type')
            ->toArray();
        $paymentRunTypeEnum = PaymentRunTypeEnum::from($this->paymentRunTypeEnum);

        $paymentRuns = [];
        foreach ($orderPayments as $type => $payments) {
            try {
                $paymentRuns[] = CreatePaymentRun::make([
                    'state' => Open::$name,
                    'payment_run_type_enum' => $paymentRunTypeEnum,
                    'sepa_mandate_type_enum' => SepaMandateTypeEnum::tryFrom($type) ?? SepaMandateTypeEnum::BASIC,
                    'orders' => $payments,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();

                $this->toast()
                    ->success(__(':model created', ['model' => __('Payment Run')]))
                    ->send();

                $this->redirectRoute('accounting.payment-runs', navigate: true);
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                foreach ($paymentRuns as $paymentRun) {
                    try {
                        DeletePaymentRun::make(['id' => $paymentRun->getKey()])
                            ->validate()
                            ->execute();
                    } catch (ValidationException $e) {
                        exception_to_notifications($e, $this);
                    }
                }

                return;
            }
        }
    }

    #[Renderless]
    public function removeOrder(int $orderId): void
    {
        unset($this->orders[$orderId]);

        if (! $this->orders) {
            $this->redirectRoute('accounting.money-transfer', navigate: true);
        }
    }
}

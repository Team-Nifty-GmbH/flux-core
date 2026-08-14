<?php

namespace FluxErp\Jobs\Accounting;

use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Models\PaymentRunPosition;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class SendPaymentAdviceJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $positionId) {}

    public function handle(): void
    {
        $position = resolve_static(PaymentRunPosition::class, 'query')
            ->whereKey($this->positionId)
            ->with(['contact.invoiceAddress', 'contact.mainAddress'])
            ->first();

        if (! $position) {
            return;
        }

        $address = filled($position->contact?->invoiceAddress?->email_primary)
            ? $position->contact->invoiceAddress
            : $position->contact?->mainAddress;

        $to = $address?->email_primary;

        if (! $to) {
            $this->abort($position, __('No recipient address'));

            return;
        }

        $result = SendMail::make([
            'to' => [$to],
            'subject' => __('Payment Advice') . ' ' . $position->end_to_end_id,
            'html_body' => __(
                'Please find attached the payment advice for the transfer with reference :reference.',
                ['reference' => $position->end_to_end_id]
            ),
            'attachments' => [
                [
                    'model_type' => $position->getMorphClass(),
                    'model_id' => $position->getKey(),
                    'view' => 'payment-advice',
                ],
            ],
            'communicatables' => [
                [
                    'model_type' => $position->getMorphClass(),
                    'model_id' => $position->getKey(),
                ],
            ],
        ])
            ->validate()
            ->execute();

        if (! data_get($result, 'success', false)) {
            $this->abort($position, data_get($result, 'error') ?? data_get($result, 'message'), [$to]);
        }
    }

    protected function abort(PaymentRunPosition $position, ?string $reason, ?array $to = null): void
    {
        activity()
            ->event('payment_advice_send_failed')
            ->byAnonymous()
            ->performedOn($position)
            ->withProperties(array_filter([
                'error' => $reason,
                'to' => $to,
            ]))
            ->log('Payment advice send failed');

        $this->fail(new RuntimeException(
            __('Payment advice for position :reference could not be sent: :reason', [
                'reference' => $position->end_to_end_id,
                'reason' => $reason ?? __('Unknown error'),
            ])
        ));
    }
}

<?php

namespace FluxErp\Jobs\Accounting;

use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\View\Printing\PaymentRun\PaymentAdvice;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Attachment;
use Illuminate\Queue\InteractsWithQueue;
use RuntimeException;

class SendPaymentAdviceJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

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

        $paymentAdvice = PaymentAdvice::make($position);

        if ($position->getMedia($paymentAdvice::MEDIA_COLLECTION)->isNotEmpty()) {
            return;
        }

        $to = $position->resolveMailableAddress()?->email_primary;

        if (! $to) {
            $this->abort($position, __('No recipient address'));

            return;
        }

        $printed = $paymentAdvice->print();

        $result = SendMail::make([
            'to' => [$to],
            'subject' => $printed->getSubject(),
            'html_body' => __(
                'Please find attached the payment advice for the transfer with reference :reference.',
                ['reference' => $position->end_to_end_id]
            ),
            'attachments' => [
                Attachment::fromData(fn () => $printed->pdf->output(), $printed->getFileName()),
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

        if (! data_get($result, 'success')) {
            $this->abort($position, data_get($result, 'error') ?? data_get($result, 'message'), [$to]);

            return;
        }

        $printed->attachToModel($position);
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

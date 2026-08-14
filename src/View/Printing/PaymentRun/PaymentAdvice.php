<?php

namespace FluxErp\View\Printing\PaymentRun;

use FluxErp\Models\PaymentRunPosition;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class PaymentAdvice extends PrintableView
{
    public const MEDIA_COLLECTION = 'payment-advice';

    public PaymentRunPosition $model;

    public function __construct(PaymentRunPosition $paymentRunPosition)
    {
        $this->model = $paymentRunPosition;
    }

    public function render(): View|Factory
    {
        return view('print::payment-run.payment-advice', [
            'model' => $this->model,
        ]);
    }

    public function getFileName(): string
    {
        return $this->getSubject();
    }

    public function getModel(): PaymentRunPosition
    {
        return $this->model;
    }

    public function getSubject(): string
    {
        return __('Payment Advice') . ' ' . $this->model->end_to_end_id;
    }

    protected function getCollectionName(): string
    {
        return self::MEDIA_COLLECTION;
    }
}

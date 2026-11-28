<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\PaymentRunTypeEnum;
use Illuminate\Support\Facades\Route;

test('the summary counts every transfer of the preview', function (): void {
    $orderIds = collect(range(1, 3))
        ->map(fn (int $i) => createPaymentRunOrder(OrderTypeEnum::Purchase, '-100.00', [
            'iban' => 'DE89370400440532013000',
            'invoice_number' => 'RE-' . $i,
        ])->getKey())
        ->all();

    Route::middleware('web')->get($uri = '/payment-run-preview-test/' . uniqid(), function () use ($orderIds) {
        session([
            'payment_run_preview_orders' => $orderIds,
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ]);

        return redirect()->route('accounting.payment-run-preview');
    });

    $page = visit($uri)->assertNoSmoke();

    waitForCondition($page, <<<'JS'
        () => document.body.innerText.includes('RE-3')
    JS);

    $page->assertSee('3 Transfers')
        ->assertNoJavascriptErrors();
});

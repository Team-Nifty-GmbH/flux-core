<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\PurchaseInvoice\CreateOrderFromPurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\ForceDeletePurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\UpdatePurchaseInvoice;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\Currency;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;

class PurchaseInvoiceForm extends FluxForm
{
    public ?string $account_holder = null;

    public ?int $approval_user_id = null;

    public ?string $bank_name = null;

    public ?string $bic = null;

    public ?int $tenant_id = null;

    public ?int $contact_id = null;

    public ?int $currency_id = null;

    public ?string $iban = null;

    #[Locked]
    public ?int $id = null;

    public ?string $invoice_date = null;

    public ?string $invoice_number = null;

    public bool $is_net = false;

    #[Locked]
    public ?int $lastLedgerAccountId = null;

    public ?int $lay_out_user_id = null;

    #[Locked]
    public $media = null;

    public ?string $mediaUrl = null;

    public ?int $order_id = null;

    public ?int $order_type_id = null;

    public ?float $payment_discount_percent = null;

    public ?string $payment_discount_target_date = null;

    public ?string $payment_target_date = null;

    public ?int $payment_type_id = null;

    public array $purchase_invoice_positions = [];

    public ?string $system_delivery_date = null;

    public ?string $system_delivery_date_end = null;

    public ?string $total_gross_price = null;

    public function fill($values): void
    {
        parent::fill($values);

        $this->payment_discount_percent = ! is_null($this->payment_discount_percent)
            ? bcmul($this->payment_discount_percent, 100)
            : null;
    }

    public function findMostUsedLedgerAccountId(): void
    {
        $this->lastLedgerAccountId = resolve_static(OrderPosition::class, 'query')
            ->whereHas(
                'ledgerAccount',
                fn (Builder $query) => $query->where('ledger_account_type_enum', LedgerAccountTypeEnum::Expense)
            )
            ->whereHas('order', fn ($query) => $query->where('contact_id', $this->contact_id))
            ->groupBy('ledger_account_id')
            ->orderByRaw('COUNT(ledger_account_id) DESC')
            ->value('ledger_account_id');
    }

    public function finish(): void
    {
        $this->save();
        $this->makeAction('create-order')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate()
            ->execute();

        $this->reset();
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);

        $this->tenant_id = resolve_static(Tenant::class, 'default')?->getKey();
        $this->currency_id = resolve_static(Currency::class, 'default')?->getKey();
    }

    public function toActionData(): array
    {
        $data = parent::toActionData();
        $data['payment_discount_percent'] = ! is_null($this->payment_discount_percent)
            ? bcdiv($this->payment_discount_percent, 100)
            : null;

        return $data;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreatePurchaseInvoice::class,
            'update' => UpdatePurchaseInvoice::class,
            'delete' => ForceDeletePurchaseInvoice::class,
            'create-order' => CreateOrderFromPurchaseInvoice::class,
        ];
    }
}

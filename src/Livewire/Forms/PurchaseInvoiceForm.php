<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\PurchaseInvoice\CreateOrderFromPurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\ForceDeletePurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\UpdatePurchaseInvoice;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\Currency;
use FluxErp\Models\OrderPosition;
use Livewire\Attributes\Locked;

class PurchaseInvoiceForm extends FluxForm
{
    public ?string $account_holder = null;

    public ?int $approval_user_id = null;

    public ?string $bank_name = null;

    public ?string $bic = null;

    public ?int $client_id = null;

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

    public ?int $payment_type_id = null;

    public array $purchase_invoice_positions = [];

    public ?string $system_delivery_date = null;

    public ?string $system_delivery_date_end = null;

    public function findMostUsedLedgerAccountId(): void
    {
        $this->lastLedgerAccountId = resolve_static(OrderPosition::class, 'query')
            ->whereHas(
                'ledgerAccount',
                fn ($query) => $query->where('ledger_account_type_enum', LedgerAccountTypeEnum::Expense)
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

        $this->client_id = Client::default()?->getKey();
        $this->currency_id = Currency::default()?->getKey();
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

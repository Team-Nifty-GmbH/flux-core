<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\PurchaseInvoice\CreateOrderFromPurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\DeletePurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\UpdatePurchaseInvoice;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\Currency;
use FluxErp\Models\OrderPosition;
use Livewire\Attributes\Locked;

class PurchaseInvoiceForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $client_id = null;

    public ?int $contact_id = null;

    public ?int $currency_id = null;

    public ?int $lay_out_user_id = null;

    public ?int $order_id = null;

    public ?int $order_type_id = null;

    public ?int $payment_type_id = null;

    public ?string $invoice_date = null;

    public ?string $system_delivery_date = null;

    public ?string $system_delivery_date_end = null;

    public ?string $invoice_number = null;

    public ?string $iban = null;

    public ?string $account_holder = null;

    public ?string $bank_name = null;

    public ?string $bic = null;

    public bool $is_net = false;

    #[Locked]
    public $media = null;

    public array $purchase_invoice_positions = [];

    public ?string $mediaUrl = null;

    #[Locked]
    public ?int $lastLedgerAccountId = null;

    protected function getActions(): array
    {
        return [
            'create' => CreatePurchaseInvoice::class,
            'update' => UpdatePurchaseInvoice::class,
            'delete' => DeletePurchaseInvoice::class,
            'create-order' => CreateOrderFromPurchaseInvoice::class,
        ];
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

        $this->client_id = Client::default()?->id;
        $this->currency_id = Currency::default()?->id;
    }

    public function findLastLedgerAccountId(): void
    {
        $this->lastLedgerAccountId = resolve_static(OrderPosition::class, 'query')
            ->whereHas(
                'ledgerAccount',
                fn ($query) => $query->where('ledger_account_type_enum', LedgerAccountTypeEnum::Expense)
            )
            ->whereHas('order', fn ($query) => $query->where('contact_id', $this->contact_id))
            ->orderByDesc('id')
            ->value('ledger_account_id');
    }
}

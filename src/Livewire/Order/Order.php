<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Actions\OrderPosition\FillOrderPositions;
use FluxErp\Htmlables\TabButton;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Language;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Services\PrintService;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;
use ZipArchive;

class Order extends Component
{
    use Actions, WithTabs;

    public array $order;

    public bool $hasUpdatedOrderPositions = false;

    public array $clients = [];

    public array $availableStates = [];

    public array $printLayouts = [];

    public array $selectedPrintLayouts = [];

    public array $priceLists = [];

    public array $paymentTypes = [];

    public array $languages = [];

    public array $paymentStates = [];

    public array $deliveryStates = [];

    public array $states = [];

    public string $tab = 'order.order-positions';

    public function mount(string $id = null): void
    {
        $order = \FluxErp\Models\Order::query()
            ->whereKey($id)
            ->with([
                'priceList:id,name,is_net',
                'addresses',
                'client:id,name',
                'contact.media',
                'contact.contactBankConnections:id,contact_id,iban',
                'currency:id,iso,name,symbol',
                'orderType:id,name,print_layouts,order_type_enum',
            ])
            ->firstOrFail();

        $this->printLayouts = $order->orderType?->print_layouts ?: [];
        $this->printLayouts = array_combine(array_map('class_basename', $this->printLayouts), $this->printLayouts);

        $this->selectedPrintLayouts = array_fill_keys(array_keys($this->printLayouts), false);

        $this->order = $order->toArray();
        $this->order['contact']['avatar_url'] = $order->contact?->getAvatarUrl();
        $this->order['invoice'] = $order->invoice()?->toArray();

        $this->priceLists = PriceList::query()
            ->get(['id', 'name'])
            ->toArray();

        $this->paymentTypes = PaymentType::query()
            ->where('client_id', $this->order['client_id'] ?? null)
            ->get(['id', 'name'])
            ->toArray();

        $this->languages = Language::query()
            ->get(['id', 'name'])
            ->toArray();

        $this->clients = Client::query()
            ->get(['id', 'name'])
            ->toArray();

        $this->getAvailableStates(['payment_state', 'delivery_state', 'state']);
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.order.' . $this->order['order_type']['order_type_enum'] ?: 'order');
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('order.order-positions')->label(__('Order positions')),
            TabButton::make('order.attachments')->label(__('Attachments')),
            TabButton::make('order.accounting')->label(__('Accounting')),
            TabButton::make('order.comments')->label(__('Comments')),
            TabButton::make('order.related')->label(__('Related processes')),
            TabButton::make('order.activities')->label(__('Activities')),
        ];
    }

    public function updatedOrderAddressInvoiceId(): void
    {
        $this->order['address_invoice'] = Address::query()
            ->whereKey($this->order['address_invoice_id'])
            ->with('contact')
            ->first()
            ->toArray();
        $this->order['payment_type_id'] = $this->order['address_invoice']['contact']['payment_type_id'] ?? null;
        $this->order['price_list_id'] = $this->order['address_invoice']['contact']['price_list_id'] ?? null;
        $this->order['language_id'] = $this->order['address_invoice']['language_id'];
        $this->order['contact_id'] = $this->order['address_invoice']['contact_id'];
        $this->order['client_id'] = $this->order['address_invoice']['client_id'];
    }

    public function updatedOrderAddressDeliveryId(): void
    {
        $this->order['address_delivery'] = Address::query()
            ->whereKey($this->order['address_delivery_id'])
            ->first()
            ->toArray();
    }

    public function updatedOrder(): void
    {
        $this->skipRender();
    }

    public function updatedHasUpdatedOrderPositions(): void
    {
        $this->skipRender();
    }

    public function save(array $orderPositions): void
    {
        $this->order['address_delivery'] = $this->order['address_delivery'] ?: [];
        $this->skipRender();
        try {
            $action = UpdateOrder::make($this->order)->checkPermission()->validate();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $order = $action->execute();
        $this->notification()->success(__('Order saved successfully!'));

        if ($this->hasUpdatedOrderPositions) {
            try {
                FillOrderPositions::make([
                    'order_id' => $order->id,
                    'order_positions' => $orderPositions,
                    'simulate' => false,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                return;
            }
        }
    }

    public function delete(): false|Redirector
    {
        $this->skipRender();

        try {
            DeleteOrder::make($this->order)
                ->checkPermission()
                ->validate()
                ->execute();

            return redirect()->route('orders');
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        return false;
    }

    public function downloadDocuments()
    {
        $selected = array_keys(array_filter($this->selectedPrintLayouts, fn ($value) => $value === true));
        if ($selected) {
            $printService = new PrintService();

            $pdfs = [];
            foreach ($selected as $view) {
                $pdfs[] = [
                    'file' => $printService->viewToPdf(
                        $view,
                        \FluxErp\Models\Order::class,
                        $this->order['id']
                    )
                        ->body(),
                    'filename' => $view . '_' . $this->order['id'] . '.pdf',
                ];
            }

            if (count($pdfs) === 1) {
                return response()->streamDownload(function () use ($pdfs) {
                    echo $pdfs[0]['file'];
                }, $pdfs[0]['filename']);
            } else {
                $zip = new ZipArchive();
                $zip->open('documents.zip', ZipArchive::CREATE);

                foreach ($pdfs as $pdf) {
                    $zip->addFromString($pdf['filename'], $pdf['file']);
                }

                $zip->close();

                return response()->download('documents.zip', $zip->filename)->deleteFileAfterSend();
            }
        }

        $this->skipRender();

        return null;
    }

    public function updatedOrderState(): void
    {
        $this->getAvailableStates('state');

        $this->skipRender();
    }

    private function getAvailableStates(array|string $fieldNames): void
    {
        $fieldNames = (array) $fieldNames;
        $model = new \FluxErp\Models\Order();

        foreach ($fieldNames as $fieldName) {
            $model->{$fieldName} = $this->order[$fieldName];
            $states = \FluxErp\Models\Order::getStatesFor($fieldName)
                ->map(function ($item) {
                    return [
                        'label' => __($item),
                        'name' => $item,
                    ];
                });

            $this->availableStates[$fieldName] = $states
                ->whereIn(
                    'name',
                    array_merge(
                        [$model->{$fieldName}],
                        $model->{$fieldName}->transitionableStates()
                    )
                )
                ->toArray();
        }
    }
}

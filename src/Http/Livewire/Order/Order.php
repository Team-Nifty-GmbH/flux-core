<?php

namespace FluxErp\Http\Livewire\Order;

use FluxErp\Http\Requests\UpdateOrderRequest;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Language;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Services\OrderPositionService;
use FluxErp\Services\OrderService;
use FluxErp\Services\PrintService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use WireUi\Traits\Actions;
use ZipArchive;

class Order extends Component
{
    use Actions;

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

    public string $tab = 'order-positions';

    public function mount(?string $id = null): void
    {
        $order = \FluxErp\Models\Order::query()
            ->whereKey($id)
            ->with([
                'priceList:id,name',
                'addresses',
                'client:id,name',
                'contact.media',
                'currency:id,iso,name',
                'orderType:id,name,print_layouts,order_type_enum',
            ])
            ->firstOrFail();

        $this->printLayouts = $order->orderType?->print_layouts ?: [];
        $this->printLayouts = array_combine(array_map('class_basename', $this->printLayouts), $this->printLayouts);

        $this->selectedPrintLayouts = array_fill_keys(array_keys($this->printLayouts), false);

        $this->order = $order->toArray();
        $this->order['contact']['avatar_url'] = $order->contact?->getAvatarUrl();

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

    public function recalculateOrder(array $orderPositions): void
    {
        $this->order['total_net_price'] = 0;
        $this->order['total_gross_price'] = 0;
        $this->order['total_vats'] = [];

        foreach ($orderPositions as $orderPosition) {
            if (data_get($orderPosition, 'is_alternative') || data_get($orderPosition, 'is_bundle_position')) {
                continue;
            }

            $this->order['total_net_price'] = bcadd($this->order['total_net_price'], $orderPosition['total_net_price']);
            $this->order['total_gross_price'] = bcadd($this->order['total_gross_price'], $orderPosition['total_gross_price']);
            $this->order['total_vats'][$orderPosition['vat_rate_id']]['total_vat_price'] =
                bcadd(
                    $this->order['total_vats'][$orderPosition['vat_rate_id']]['total_vat_price'] ?? 0,
                    bcsub($orderPosition['total_gross_price'], $orderPosition['total_net_price'])
                );
            $this->order['total_vats'][$orderPosition['vat_rate_id']]['vat_rate_percentage'] = $orderPosition['vat_rate_percentage'];
        }

        $this->skipRender();
    }

    public function save(array $orderPositions): void
    {
        $validatedOrder = Validator::make($this->order, (new UpdateOrderRequest())->getRules($this->order))
            ->validate();
        $responseOrder = (new OrderService())->update($validatedOrder);
        if ($responseOrder['status'] === 200) {
            $this->notification()->success(__('Order saved successfully!'));
        } else {
            $flattened = Arr::dot($responseOrder['errors']);
            foreach ($flattened as $key => $error) {
                $this->notification()->error($key, $error);
            }
        }

        if ($this->hasUpdatedOrderPositions) {
            $response = (new OrderPositionService())->fill($this->order['id'], to_tree($orderPositions));
            if ($response['status'] === 200) {
                $this->notification()->success(__('Order positions saved successfully!'));
                $this->hasUpdatedOrderPositions = false;
            } else {
                $flattened = Arr::dot($response['errors']);
                foreach ($flattened as $key => $error) {
                    $this->notification()->error($key, $error);
                }
            }
        }

        $this->skipRender();
    }

    public function delete(): void
    {
        // TODO: Implement delete() method.
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
                        'label' => __(ucfirst(str_replace('_', ' ', $item))),
                        'name' => $item,
                    ];
                });

            $this->availableStates[$fieldName] = $states
                ->whereIn('name', $model->{$fieldName}->transitionableStates())
                ->toArray();
        }
    }
}

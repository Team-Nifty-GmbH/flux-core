<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Actions\OrderPosition\FillOrderPositions;
use FluxErp\Actions\Printing;
use FluxErp\Htmlables\TabButton;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Language;
use FluxErp\Models\Media;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Traits\Livewire\WithTabs;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use WireUi\Traits\Actions;

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

    public function mount(?string $id = null): void
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
                'orderType:id,name,mail_subject,mail_body,print_layouts,order_type_enum',
            ])
            ->firstOrFail();

        $this->printLayouts = array_intersect(
            $order->orderType?->print_layouts ?: [],
            array_keys($order->resolvePrintViews())
        );

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

    public function delete(): void
    {
        $this->skipRender();

        try {
            DeleteOrder::make($this->order)
                ->checkPermission()
                ->validate()
                ->execute();

            $this->redirect(route('orders.orders'), true);
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }
    }

    #[Renderless]
    public function downloadPreview(string $view): ?StreamedResponse
    {
        try {
            $pdf = Printing::make([
                'model_type' => \FluxErp\Models\Order::class,
                'model_id' => $this->order['id'],
                'view' => $view,
                'preview' => true,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return null;
        }

        return response()->streamDownload(
            fn () => print($pdf->pdf->output()),
            Str::finish($pdf->getFileName(), '.pdf')
        );
    }

    #[Renderless]
    public function createDocuments(): null|MediaStream|Media
    {
        $order = \FluxErp\Models\Order::query()
            ->whereKey($this->order['id'])
            ->with('addresses')
            ->first();
        $hash = md5(json_encode($order->toArray()) . json_encode($order->orderPositions->toArray()));

        $createDocuments = [];
        foreach ($this->selectedPrintLayouts as $type => $selectedPrintLayout) {
            $this->selectedPrintLayouts[$type] = array_filter($selectedPrintLayout);
            $createDocuments = array_unique(
                array_merge(
                    $createDocuments,
                    array_keys($this->selectedPrintLayouts[$type]))
            );
        }

        // create the documents
        $mediaIds = [];
        $downloadIds = [];
        $printIds = [];
        $mailAttachments = [];
        foreach ($createDocuments as $createDocument) {
            $media = $order->getMedia(
                $createDocument,
                fn (BaseMedia $media) => $media->getCustomProperty('hash') === $hash
            )
                ->last();

            if (! $media || ($this->selectedPrintLayouts['force'][$createDocument] ?? false)) {
                try {
                    /** @var PrintableView $file */
                    $file = Printing::make([
                        'model_type' => \FluxErp\Models\Order::class,
                        'model_id' => $this->order['id'],
                        'view' => $createDocument,
                    ])->checkPermission()->validate()->execute();

                    $media = $file->attachToModel();
                    $media->setCustomProperty('hash', $hash)->save();
                } catch (ValidationException|UnauthorizedException $e) {
                    exception_to_notifications($e, $this);

                    continue;
                }
            }

            $mediaIds[$createDocument] = $media->id;

            if ($this->selectedPrintLayouts['download'][$createDocument] ?? false) {
                $downloadIds[] = $media->id;
            }

            if ($this->selectedPrintLayouts['print'][$createDocument] ?? false) {
                // TODO: add to print queue for spooler
                $printIds[] = $media->id;
            }

            if ($this->selectedPrintLayouts['email'][$createDocument] ?? false) {
                $mailAttachments[] = [
                    'name' => $media->file_name,
                    'id' => $media->id,
                ];
            }
        }

        if (($this->selectedPrintLayouts['email'] ?? false) && $mailAttachments) {
            $to = [];

            $to[] = in_array('invoice', $createDocuments) && $order->contact->invoiceAddress
                ? $order->contact->invoiceAddress->email
                : $order->contact->mainAddress->email;

            if (array_keys($this->selectedPrintLayouts['email']) !== ['invoice']
                && $order->contact->mainAddress->email
            ) {
                $to[] = $order->contact->mainAddress->email;
            }

            $this->dispatch(
                'create',
                [
                    'to' => array_unique($to),
                    'subject' => Blade::render(
                        html_entity_decode($this->order['order_type']['mail_subject']),
                        ['order' => $order]
                    ) ?: $this->order['order_type']['name'] . ' ' . $this->order['order_number'],
                    'attachments' => $mailAttachments,
                    'html_body' => Blade::render(
                        html_entity_decode($this->order['order_type']['mail_body']),
                        ['order' => $order]
                    ),
                ]
            )->to('edit-mail');
        }

        if ($downloadIds) {
            $files = Media::query()
                ->whereIntegerInRaw('id', $downloadIds)
                ->get();

            if ($files->count() === 1) {
                return $files->first();
            }

            return MediaStream::create($this->order['order_type']['name'] . '_' . $this->order['order_number'] . '.zip')
                ->addMedia($files);
        }

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

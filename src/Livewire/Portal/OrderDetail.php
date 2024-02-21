<?php

namespace FluxErp\Livewire\Portal;

use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\TicketType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderDetail extends Component
{
    public array $order = [];

    public array $childOrders = [];

    public array $attachments = [];

    public array $positionDetails = [];

    public array $positionsSummary = [];

    public array $selected = [];

    public bool $detailModal = false;

    public array $enabledCols;

    public array $availableCols;

    public array $childEnabledCols;

    public array $childAvailableCols;

    public array $ticketTypes = [];

    private array $tree;

    public function mount(string $id): void
    {
        $this->ticketTypes = TicketType::all()
            ->pluck('name', 'id')
            ->toArray();

        $order = Order::query()
            ->whereKey($id)
            ->with(
                [
                    'addresses',
                    'orderType',
                    'addressInvoice.contact',
                    'addressDelivery',
                    'orderPositions',
                    'currency:id,iso',
                ]
            )
            ->first();

        if (! $order || ! $order->is_locked || $order->contact_id !== auth()->user()->contact_id) {
            abort(404);
        }

        $this->enabledCols = ['slug_position', 'name'];
        $this->enabledCols = array_merge(
            $this->enabledCols,
            auth()->user()->contact?->priceList?->is_net
                ? [
                    'amount',
                    'unit_net_price',
                    'discount_percentage',
                    'total_net_price',
                ]
                : [
                    'amount',
                    'unit_gross_price',
                    'discount_percentage',
                    'total_gross_price',
                ]
        );

        $this->availableCols = array_merge(
            $this->enabledCols,
            [
                'id',
                'total_net_price',
                'unit_net_price',
                'total_gross_price',
                'unit_gross_price', ]
        );

        // get the latest attachment per collection_name where disk is public
        $this->attachments = $order->media()
            ->select(['id', 'collection_name', 'file_name', 'mime_type', 'disk'])
            ->where('disk', 'public')
            ->where('collection_name', '!=', 'default')
            ->whereIn('id', function ($query) use ($order) {
                return $query->selectRaw('MAX(id)')
                    ->from('media')
                    ->where('model_id', $order->id)
                    ->where('model_type', Order::class)
                    ->where('disk', 'public')
                    ->groupBy('collection_name');
            })
            ->get()
            ->toArray();

        $this->order = $order->toArray();

        $orderArray = array_intersect_key($this->order, array_flip(
            [
                'id',
                'parent_id',
                'order_number',
                'commission',
                'payment_target',
                'payment_discount_tar',
                'header',
                'footer',
                'logistic_note',
                'payment_texts',
                'order_date',
                'invoice_date',
                'invoice_number',
                'system_delivery_date',
                'customer_delivery_date',
                'date_of_approval',
                'is_confirmed',
                'is_paid',
                'total_net_price',
                'total_gross_price',
                'total_vats',
                'order_type',
                'address_invoice',
                'currency',
            ]
        ));

        $positions = OrderPosition::query()
            ->where('order_id', $order->id)
            ->whereNull('parent_id')
            ->with('tags')
            ->get()
            ->append('children')
            ->each(function (OrderPosition $position) {
                return $position->setVisible($this->availableCols);
            });

        $this->renderTree($positions);
        $this->tree = to_flat_tree($positions->toArray());

        $this->order = $orderArray;
        $this->order['order_positions'] = $this->tree;

        $this->childOrders = $order->children?->toArray();
        $this->childEnabledCols = ['order_number', 'commission'];
        $this->childEnabledCols[] = auth()->user()->contact?->priceList?->is_net
            ? 'total_net_price'
            : 'total_gross_price';
        $this->childAvailableCols = array_merge(
            $this->childEnabledCols,
            [
                'order_date',
                'total_net_price',
                'total_gross_price',
                'invoice_number',
                'invoice_date',
            ]
        );

        $intersect = array_flip(array_merge($this->childAvailableCols, ['currency_id', 'id']));
        foreach ($this->childOrders as $key => $childOrder) {
            $this->childOrders[$key] = array_intersect_key($childOrder, $intersect);
        }
    }

    public function render(): mixed
    {
        return view('flux::livewire.portal.order-detail');
    }

    public function updated(): void
    {
        $this->skipRender();
    }

    public function selectPosition(int $id): void
    {
        $position = OrderPosition::query()
            ->whereKey($id)
            ->first();

        $image = $position->product?->getFirstMedia('images')
            ?? $position->product?->parent?->getFirstMedia('images');

        $serialNumber = $position->serialNumbers()->select(['id', 'serial_number'])->get()
            ?: $position->origin?->serialNumbers()->select(['id', 'serial_number'])->get();

        $this->positionDetails = $position->toArray();
        $this->positionDetails['serial_number'] = $serialNumber->toArray();
        $this->positionDetails['product'] = $position
            ->product()
            ->select(['id', 'description', 'product_number'])
            ->first()
            ?->toArray();

        $this->positionDetails['image'] = $image?->toHtml();
        $this->detailModal = true;

        $this->dispatch('renderFromTree', $position->product?->getMediaAsTree())->to('folder-tree');
    }

    public function downloadInvoice(): BinaryFileResponse
    {
        $order = Order::query()
            ->whereKey($this->order['id'])
            ->first();
        $mediaItem = $order->invoice();

        if (! $mediaItem) {
            abort(404);
        }

        activity()->performedOn($order)
            ->event('downloaded')
            ->log($mediaItem->collection_name . ' ' . $mediaItem->name);

        return response()->download($mediaItem->getPath(), $mediaItem->name);
    }

    public function downloadMedia(int $id): BinaryFileResponse
    {
        $order = Order::query()
            ->whereKey($this->order['id'])
            ->first();

        $mediaItem = $order->media()->whereKey($id)->first();

        if (! $mediaItem) {
            abort(404);
        }

        activity()->performedOn($order)
            ->event('downloaded')
            ->log($mediaItem->collection_name . ' ' . $mediaItem->name);

        return response()->download($mediaItem->getPath(), $mediaItem->name);
    }

    private function renderTree(array|Collection $tree, int $level = 0, string $loopPrefix = '', $parent = null): void
    {
        $loop = 1;

        foreach ($tree as $item) {
            $treeItem = is_array($item) ? collect($item) : $item;

            if ($treeItem->is_alternative || ! $treeItem->children->count() && $treeItem->is_no_product) {
                $treeItem->total_net_price = null;
                $treeItem->total_gross_price = null;
            }

            $treeItem->level = $level;
            $treeItem->is_alternative = $parent?->is_alternative ?: $treeItem->is_alternative;
            $treeItem->name = '<div><div>'
                . $treeItem->product?->product_number .
                '</div><div class="font-semibold">'
                . $treeItem->name .
                '</div></div>';

            if ($treeItem->is_alternative && ! ($treeItem->tags->isEmpty() ?? false)) {
                $tagItems = '';
                foreach ($treeItem->tags as $tag) {
                    $tagItems .= '<div>' . $tag->name . '</div>';
                }

                $treeItem->name = '<div><div class="flex">' . $tagItems . '</div>' . $treeItem->name . '</div>';
            }

            $treeItem->name = $level > 0
                ? '<i class="fa-regular fa-arrow-turn-down-right pr-2"  style="padding-left: '
                    . $level * 30 . 'px;"></i>'
                    . $treeItem['name']
                : $treeItem['name'];

            // show line number if
            // 1. the item is at the top level, has no parent
            // 2. the parent is not a product
            // 3. the item has children
            if (! $parent || $parent->is_no_product && ! $treeItem->is_no_product || $treeItem->children->count()) {
                $treeItem->pos = $loopPrefix . Str::padLeft($loop, 2, '0');
            }

            if ($level === 0 && $treeItem->is_no_product && $treeItem->children->count()) {
                $this->positionsSummary[] = $treeItem->toArray();
                $treeItem->is_block = true;
            }

            $intersect = array_flip(array_merge($this->availableCols, ['id']));

            $this->tree[] = array_intersect_key($treeItem->toArray(), $intersect);

            unset($this->tree[count($this->tree) - 1]['children']);
            if ($treeItem->children) {
                $this->renderTree(
                    $treeItem->children,
                    $level + 1,
                    Str::padLeft($loop, 2, '0') . '.',
                    $treeItem
                );
            }

            $loop++;
        }
    }
}

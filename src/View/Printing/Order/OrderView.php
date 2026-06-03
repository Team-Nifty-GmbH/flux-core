<?php

namespace FluxErp\View\Printing\Order;

use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Scopes\FamilyTreeScope;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

class OrderView extends PrintableView
{
    public Order $model;

    public array $summary = [];

    protected bool $showAlternatives = true;

    public function __construct(Order $order)
    {
        app()->setLocale($order->language?->language_code
            ?? $order->addressInvoice?->language?->language_code
            ?? config('app.locale')
        );
        Number::useLocale(app()->getLocale());
        if ($orderCurrency = $order->currency()->withTrashed()->value('iso')) {
            Number::useCurrency($orderCurrency);
        }

        $this->model = $order;
        $this->model->orderType?->localize($order->language_id);
        $this->prepareModel();
    }

    public static function isInvoice(): bool
    {
        return false;
    }

    public function render(): View|Factory
    {
        return view('print::order.order', [
            'model' => $this->model,
            'summary' => $this->summary,
        ]);
    }

    public function getFileName(): string
    {
        return $this->getSubject();
    }

    public function getModel(): Order
    {
        return $this->model;
    }

    public function getSubject(): string
    {
        return __('Order') . ' ' . $this->model->order_number;
    }

    public function prepareModel(): void
    {
        $positions = array_map(
            fn (array $item) => app(OrderPosition::class)
                ->forceFill(array_replace($item, ['slug_position' => $item['new_slug_position'] ?? null])),
            to_flat_tree(
                $this->reorderSlugPositions(
                    resolve_static(OrderPosition::class, 'withTemporaryGlobalScopes', [
                        'scopes' => [
                            'sorted' => function (Builder $query): void {
                                $query->ordered()
                                    ->with([
                                        'tags',
                                        'product' => fn (BelongsTo $query) => $query
                                            ->withTrashed()
                                            ->with('unit:id,name,abbreviation'),
                                    ])
                                    ->when(
                                        ! $this->showAlternatives,
                                        fn (Builder $query) => $query->whereNot('is_alternative', true)
                                    );
                            },
                            resolve_static(FamilyTreeScope::class, 'class') => app(FamilyTreeScope::class),
                        ],
                    ])
                        ->where('order_id', $this->model->getKey())
                        ->whereNull('parent_id')
                        ->get()
                        ->toArray()
                )
            )
        );

        $flattened = app(OrderPosition::class)->newCollection($positions);

        foreach ($flattened as $item) {
            if ($item->depth === 0 && $item->is_free_text && $item->has_children) {
                $this->summary[] = $item;
            }
        }

        $this->model->setRelation('orderPositions', $flattened);
    }

    protected function reorderSlugPositions(array $positions, ?string $parentSlug = null): array
    {
        $index = 0;
        foreach ($positions as &$position) {
            $slug = null;
            if (data_get($position, 'total_net_price')) {
                $index++;
                $segment = str_pad((string) $index, 8, '0', STR_PAD_LEFT);
                $position['new_slug_position'] = $slug = is_null($parentSlug) ? $segment : $parentSlug . '.' . $segment;
            }

            if (count($position['children'] ?? []) > 0) {
                $position['children'] = $this->reorderSlugPositions(
                    $position['children'],
                    $slug ?? $position['slug_position']
                );
            }
        }

        return $positions;
    }
}

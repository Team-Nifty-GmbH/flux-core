<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Actions\OrderPosition\PriceCalculation;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\Tenant;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use Illuminate\Database\Seeder;

class OrderPositionTableSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::query()->with('orderType:id,order_type_enum')->get();
        $products = Product::query()
            ->with(['prices'])
            ->get();
        $suppliers = Contact::all(['id']);
        $warehouses = Warehouse::all(['id']);
        $vatRates = VatRate::all(['id']);
        $tenantId = Tenant::default()?->id ?? Tenant::query()->value('id');

        foreach ($orders as $order) {
            $multiplier = $order->orderType->order_type_enum->multiplier();
            $orderPositions = OrderPosition::query()->where('order_id', $order->id)->get();
            for ($i = 0; $i < 10; $i++) {
                $product = $products->isEmpty() ? null : $products->random();
                $price = $product->prices->isEmpty() ? null : $product->prices->random();
                $supplier = $suppliers->isEmpty() ? null : $suppliers->random();
                $warehouse = $warehouses->isEmpty() ? null : $warehouses->random();
                $vatRate = $vatRates->isEmpty() ? null : $vatRates->random();
                $sortNumber = $orderPositions->count();

                if (rand(0, 9) < 7) { // Default order position
                    $orderPosition = OrderPosition::factory()->make([
                        'tenant_id' => $order->tenant_id ?? $tenantId,
                        'order_id' => $order->id,
                        'parent_id' => rand(0, 1) ?
                            (
                                $orderPositions
                                    ->whereNull('parent_id')
                                    ->where('is_bundle', false)
                                    ->where('is_bundle_position', false)
                                    ->where('is_free_text', true)
                                    ->isEmpty() ?
                                    null :
                                    $orderPositions
                                        ->whereNull('parent_id')
                                        ->where('is_bundle', false)
                                        ->where('is_bundle_position', false)
                                        ->where('is_free_text', true)
                                        ->random()->id
                            ) : null,
                        'product_id' => $productId = rand(0, 3) < 3 ? ($product->id ?? null) : null,
                        'price_id' => $productId ? ($price->id ?? null) : null,
                        'price_list_id' => $productId ? ($price->price_list_id ?? null) : null,
                        'supplier_contact_id' => $supplier->id ?? null,
                        'vat_rate_id' => $vatRate->id ?? null,
                        'warehouse_id' => $warehouse->id ?? null,
                        'vat_rate_percentage' => $vatRate->rate_percentage ?? null,
                        'ean_code' => $productId ? ($product->ean ?? null) : null,
                        'unit_gram_weight' => $productId ? ($product->weight_gram ?? null) : null,
                        'description' => $productId ? ($product->description ?? null) : null,
                        'name' => $product->name ?? null,
                        'product_number' => $productId ? ($product->product_number ?? null) : null,
                        'sort_number' => $sortNumber,
                        'is_free_text' => ! $productId,
                    ]);

                    $data = $orderPosition->toArray();

                    $data['unit_price'] = bcmul(rand(1, 9999) / 100, $multiplier);

                    PriceCalculation::make($orderPosition, $data)->calculate();
                    unset($orderPosition->unit_price);
                    $orderPosition->save();

                    // Create Bundle Positions
                    if ($orderPosition->product_id && $product->is_bundle) {
                        foreach ($product->bundleProducts as $index => $bundleProduct) {
                            OrderPosition::factory()->create([
                                'tenant_id' => $orderPosition->tenant_id,
                                'order_id' => $orderPosition->order_id,
                                'parent_id' => $orderPosition->id,
                                'product_id' => $bundleProduct->id,
                                'amount' => bcmul($bundleProduct->pivot->count, $orderPosition->amount),
                                'amount_bundle' => $bundleProduct->pivot->count,
                                'discount_percentage' => null,
                                'margin' => null,
                                'provision' => null,
                                'purchase_price' => null,
                                'ean_code' => $bundleProduct->ean,
                                'unit_gram_weight' => $bundleProduct->weight_gram,
                                'description' => $bundleProduct->description,
                                'name' => $bundleProduct->name,
                                'product_number' => $bundleProduct->product_number,
                                'sort_number' => $sortNumber + $index + 1,
                                'is_free_text' => true,
                                'is_bundle_position' => true,
                            ]);
                        }
                    }
                } else { // Block / Free text
                    OrderPosition::factory()->create([
                        'tenant_id' => $order->tenant_id ?? $tenantId,
                        'order_id' => $order->id,
                        'parent_id' => rand(0, 1) ?
                            ($orderPositions->isEmpty() ?
                                null : $orderPositions->random()->id
                            ) : null,
                        'amount' => null,
                        'discount_percentage' => null,
                        'margin' => null,
                        'provision' => null,
                        'purchase_price' => null,
                        'sort_number' => $sortNumber,
                        'is_free_text' => true,
                    ]);
                }
            }
        }

        $orders->each(fn (Order $order) => $order->calculatePrices()->save());
    }
}

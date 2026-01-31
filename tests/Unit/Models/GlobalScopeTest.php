<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Scopes\FamilyTreeScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $scopes = OrderPosition::getAllGlobalScopes();
    unset($scopes[OrderPosition::class]['sorted']);
    unset($scopes[OrderPosition::class][FamilyTreeScope::class]);
    OrderPosition::setAllGlobalScopes($scopes);
});

afterEach(function (): void {
    $scopes = OrderPosition::getAllGlobalScopes();
    unset($scopes[OrderPosition::class]['sorted']);
    unset($scopes[OrderPosition::class][FamilyTreeScope::class]);
    OrderPosition::setAllGlobalScopes($scopes);
});

it('applies temporary scopes during eager loading and removes them after', function (): void {
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
    ]);

    $orderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $paymentType = PaymentType::factory()->create();
    $paymentType->tenants()->attach($this->dbTenant->getKey());

    $order = Order::factory()->create([
        'currency_id' => Currency::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'address_invoice_id' => $address->getKey(),
    ]);

    $parent = OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'parent_id' => $parent->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    DB::enableQueryLog();

    $result = resolve_static(OrderPosition::class, 'withTemporaryGlobalScopes', [
        'scopes' => [
            'sorted' => function (Builder $query): void {
                $query->ordered();
            },
            FamilyTreeScope::class => app(FamilyTreeScope::class),
        ],
    ])->where('order_id', $order->getKey())
        ->whereNull('parent_id')
        ->get();

    $queries = collect(DB::getQueryLog());
    DB::disableQueryLog();

    // 1. Eager loading fired and the children relation was loaded
    expect($result)->toHaveCount(1)
        ->and($result->first()->relationLoaded('children'))->toBeTrue()
        ->and($result->first()->children)->toHaveCount(1);

    // 2. The eager loading query for children had ORDER BY sort_number (sorted scope was active)
    $childrenQuery = $queries->first(
        fn (array $query) => str_contains($query['query'], 'parent_id') && str_contains($query['query'], 'sort_number')
    );
    expect($childrenQuery)->not->toBeNull();

    // 3. After get(), the scopes are gone - a subsequent query must not contain sort_number
    $sql = resolve_static(OrderPosition::class, 'query')
        ->groupBy(['vat_rate_percentage', 'vat_rate_id'])
        ->selectRaw('sum(total_net_price) as total_net_price, vat_rate_percentage, vat_rate_id')
        ->toRawSql();

    expect($sql)->not->toContain('sort_number');
});

it('does not leak a sorted scope into subsequent queries after removeGlobalScopes', function (): void {
    resolve_static(OrderPosition::class, 'addGlobalScope', [
        'scope' => 'sorted',
        'implementation' => function (Builder $query): void {
            $query->ordered();
        },
    ]);

    resolve_static(OrderPosition::class, 'removeGlobalScopes', [
        'scopes' => ['sorted'],
    ]);

    $sql = resolve_static(OrderPosition::class, 'query')
        ->groupBy(['vat_rate_percentage', 'vat_rate_id'])
        ->selectRaw('sum(total_net_price) as total_net_price, vat_rate_percentage, vat_rate_id')
        ->toRawSql();

    expect($sql)->not->toContain('sort_number');
});

<?php

use FluxErp\Actions\Record\MergeRecords;
use FluxErp\Models\Address;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Lead;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\RecordOrigin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Scout\Jobs\MakeSearchable;

function createMergeOrder(object $test, array $attributes = []): Order
{
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'language_id' => $test->defaultLanguage->getKey(),
    ]);

    return Order::factory()->create(array_merge([
        'contact_id' => $contact->getKey(),
        'address_invoice_id' => $address->getKey(),
        'address_delivery_id' => $address->getKey(),
        'language_id' => $test->defaultLanguage->getKey(),
        'tenant_id' => $test->dbTenant->getKey(),
        'order_type_id' => data_get($attributes, 'order_type_id') ?? OrderType::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'payment_type_id' => PaymentType::factory()->create()->getKey(),
    ], $attributes));
}

function cloneOrders(Order $order, int $count): void
{
    $row = (array) DB::table('orders')->where('id', $order->getKey())->first();
    unset($row['id']);

    foreach (array_chunk(range(1, $count), 250) as $numbers) {
        DB::table('orders')->insert(array_map(
            fn (int $number) => array_merge($row, [
                'uuid' => Str::uuid()->toString(),
                'order_number' => 'clone-' . $number,
            ]),
            $numbers
        ));
    }
}

function runMerge(string $modelClass, int $mainId, int $mergeId): void
{
    MergeRecords::make([
        'model_type' => morph_alias($modelClass),
        'main_record' => ['id' => $mainId, 'columns' => []],
        'merge_records' => [['id' => $mergeId, 'columns' => []]],
    ])
        ->validate()
        ->execute();
}

test('merges addresses when categorizable_id coincides with another model id', function (): void {
    $contact = Contact::factory()->create();

    $mainAddress = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_main_address' => false,
    ]);

    $mergeAddress = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_main_address' => false,
    ]);

    $category = Category::factory()->create([
        'model_type' => morph_alias(Contact::class),
    ]);

    // Categorizable entry matching the merge address ID triggers NOT NULL violation on categorizable_type.
    DB::table('categorizable')->insert([
        'category_id' => $category->getKey(),
        'categorizable_type' => morph_alias(Contact::class),
        'categorizable_id' => $mergeAddress->getKey(),
    ]);

    $result = MergeRecords::make([
        'model_type' => morph_alias(Address::class),
        'main_record' => [
            'id' => $mainAddress->getKey(),
            'columns' => [],
        ],
        'merge_records' => [
            [
                'id' => $mergeAddress->getKey(),
                'columns' => [],
            ],
        ],
    ])
        ->checkPermission()
        ->validate()
        ->execute();

    expect($result->getKey())->toBe($mainAddress->getKey());
    expect(Address::query()->whereKey($mergeAddress->getKey())->first())->toBeNull();
});

test('merging contacts keeps exactly one main, invoice and delivery address', function (): void {
    $target = Contact::factory()->create();
    $targetMain = Address::factory()->create([
        'contact_id' => $target->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);
    $target->update([
        'main_address_id' => $targetMain->getKey(),
        'invoice_address_id' => $targetMain->getKey(),
        'delivery_address_id' => $targetMain->getKey(),
    ]);

    $source = Contact::factory()->create();
    $sourceMain = Address::factory()->create([
        'contact_id' => $source->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);
    Address::factory()->count(2)->create([
        'contact_id' => $source->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_main_address' => false,
        'is_invoice_address' => false,
        'is_delivery_address' => false,
    ]);
    $source->update([
        'main_address_id' => $sourceMain->getKey(),
        'invoice_address_id' => $sourceMain->getKey(),
        'delivery_address_id' => $sourceMain->getKey(),
    ]);

    MergeRecords::make([
        'model_type' => morph_alias(Contact::class),
        'main_record' => [
            'id' => $target->getKey(),
            'columns' => [],
        ],
        'merge_records' => [
            [
                'id' => $source->getKey(),
                'columns' => [],
            ],
        ],
    ])
        ->checkPermission()
        ->validate()
        ->execute();

    $target->refresh();
    $addresses = Address::query()->where('contact_id', $target->getKey())->get();

    expect($addresses)->toHaveCount(4)
        ->and($addresses->where('is_main_address', true)->pluck('id')->all())
        ->toBe([$target->main_address_id])
        ->and($addresses->where('is_invoice_address', true)->pluck('id')->all())
        ->toBe([$target->invoice_address_id])
        ->and($addresses->where('is_delivery_address', true)->pluck('id')->all())
        ->toBe([$target->delivery_address_id]);
});

test('merging order types moves many orders without loading them', function (): void {
    $main = OrderType::factory()->create();
    $merge = OrderType::factory()->create();

    cloneOrders(
        createMergeOrder($this, [
            'order_type_id' => $merge->getKey(),
            'header' => str_repeat('x', 25000),
            'footer' => str_repeat('y', 25000),
        ]),
        1499
    );

    $queries = 0;
    DB::listen(function () use (&$queries): void {
        $queries++;
    });

    gc_collect_cycles();
    $baseline = memory_get_usage();
    memory_reset_peak_usage();

    runMerge(OrderType::class, $main->getKey(), $merge->getKey());

    $peak = memory_get_peak_usage() - $baseline;

    expect(Order::query()->where('order_type_id', $main->getKey())->count())->toBe(1500)
        ->and(Order::query()->where('order_type_id', $merge->getKey())->count())->toBe(0)
        ->and(OrderType::query()->whereKey($merge->getKey())->exists())->toBeFalse()
        ->and($peak)->toBeLessThan(30 * 1024 * 1024)
        ->and($queries)->toBeLessThan(200);
});

test('merging order types keeps the audit trail of every moved order', function (): void {
    $main = OrderType::factory()->create();
    $merge = OrderType::factory()->create();
    $order = createMergeOrder($this, ['order_type_id' => $merge->getKey()]);

    DB::table('orders')->where('id', $order->getKey())->update(['updated_by' => null]);

    runMerge(OrderType::class, $main->getKey(), $merge->getKey());

    $activity = DB::table('activity_log')
        ->where('subject_type', morph_alias(Order::class))
        ->where('subject_id', $order->getKey())
        ->where('event', 'updated')
        ->sole();

    expect(json_decode($activity->attribute_changes, true))->toBe([
        'old' => ['order_type_id' => $merge->getKey()],
        'attributes' => ['order_type_id' => $main->getKey()],
    ])
        ->and($activity->log_name)->toBe('model_events')
        ->and($activity->description)->toBe('updated')
        ->and($activity->causer_type)->toBe($this->user->getMorphClass())
        ->and($activity->causer_id)->toBe($this->user->getKey())
        ->and($activity->created_at)->not->toBeNull()
        ->and(DB::table('orders')->where('id', $order->getKey())->value('updated_by'))
        ->toBe($this->user->getMorphClass() . ':' . $this->user->getKey());
});

test('merging order types reindexes the moved orders', function (): void {
    $main = OrderType::factory()->create();
    $merge = OrderType::factory()->create();
    $order = createMergeOrder($this, ['order_type_id' => $merge->getKey()]);

    config(['scout.queue' => true]);
    Queue::fake();

    runMerge(OrderType::class, $main->getKey(), $merge->getKey());

    Queue::assertPushed(
        MakeSearchable::class,
        fn (MakeSearchable $job) => $job->models->first() instanceof Order
            && $job->models->first()->order_type_id === $main->getKey()
            && $job->models->modelKeys() === [$order->getKey()]
    );
});

test('merging order types leaves soft deleted orders untouched', function (): void {
    $main = OrderType::factory()->create();
    $merge = OrderType::factory()->create();
    $order = createMergeOrder($this, ['order_type_id' => $merge->getKey()]);
    $order->delete();

    runMerge(OrderType::class, $main->getKey(), $merge->getKey());

    expect(DB::table('orders')->where('id', $order->getKey())->value('order_type_id'))
        ->toBe($merge->getKey());
});

test('merging record origins moves contacts and leads by query', function (): void {
    $main = RecordOrigin::factory()->create(['model_type' => morph_alias(Contact::class)]);
    $merge = RecordOrigin::factory()->create(['model_type' => morph_alias(Contact::class)]);
    $contact = Contact::factory()->create(['record_origin_id' => $merge->getKey()]);
    $lead = Lead::factory()->create([
        'address_id' => Address::factory()->create([
            'contact_id' => $contact->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
        ])->getKey(),
        'record_origin_id' => $merge->getKey(),
    ]);

    runMerge(RecordOrigin::class, $main->getKey(), $merge->getKey());

    expect($contact->refresh()->record_origin_id)->toBe($main->getKey())
        ->and($lead->refresh()->record_origin_id)->toBe($main->getKey());
});

test('merging addresses still runs the order save hooks', function (): void {
    $mainContact = Contact::factory()->create();
    $mainAddress = Address::factory()->create([
        'contact_id' => $mainContact->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);
    $order = createMergeOrder($this);
    $mergeAddressId = $order->address_invoice_id;

    runMerge(Address::class, $mainAddress->getKey(), $mergeAddressId);

    expect($order->refresh()->address_invoice_id)->toBe($mainAddress->getKey())
        ->and($order->contact_id)->toBe($mainContact->getKey());
});

test('merging records with save hooks does not hold every moved model in memory', function (): void {
    $main = Contact::factory()->create();
    $order = createMergeOrder($this, [
        'header' => str_repeat('x', 100000),
        'footer' => str_repeat('y', 100000),
    ]);
    cloneOrders($order, 299);

    gc_collect_cycles();
    $baseline = memory_get_usage();

    runMerge(Contact::class, $main->getKey(), $order->contact_id);

    gc_collect_cycles();
    $retained = memory_get_usage() - $baseline;

    expect(Order::query()->where('contact_id', $main->getKey())->count())->toBe(300)
        ->and($retained)->toBeLessThan(20 * 1024 * 1024);
});

<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\View\Layouts\App;
use FluxErp\View\PageTitle;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

function renderedTitle($response): ?string
{
    preg_match('/<title>(.*?)<\/title>/s', $response->getContent(), $matches);

    return isset($matches[1]) ? trim($matches[1]) : null;
}

beforeEach(function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->id]);

    $this->order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => Language::factory()->create()->id,
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
        ])->id,
        'payment_type_id' => PaymentType::factory()
            ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
            ->create(['is_default' => false])
            ->id,
        'price_list_id' => PriceList::factory()->create()->id,
        'currency_id' => Currency::factory()->create(['is_default' => true])->id,
        'address_invoice_id' => $address->id,
        'address_delivery_id' => $address->id,
        'is_locked' => false,
    ]);
});

test('a list page is titled after its route', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('orders.list.get', 'web'));

    $response = $this->actingAs($this->user, 'web')->get('/orders/list')->assertOk();

    expect(renderedTitle($response))->toBe(__('Orders') . ' / ' . config('app.name'));
});

test('a detail page carries the record it shows', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'web'));

    $response = $this->actingAs($this->user, 'web')
        ->get('/orders/' . $this->order->id)
        ->assertOk();

    expect(renderedTitle($response))
        ->toBe(__('Order') . ' / ' . e($this->order->getLabel()) . ' / ' . config('app.name'));
});

test('a record that repeats its section drops the section', function (): void {
    $this->order->orderType->update(['name' => __('Order') . ' XY']);
    $this->user->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'web'));

    $response = $this->actingAs($this->user, 'web')
        ->get('/orders/' . $this->order->id)
        ->assertOk();

    expect(renderedTitle($response))
        ->toBe(e($this->order->fresh()->getLabel()) . ' / ' . config('app.name'));
});

test('a title passed in wins over the route', function (): void {
    Route::get('/page-title-explicit', fn () => '')
        ->name('orders.id')
        ->metadata(['model' => 'order']);

    Request::setRouteResolver(fn () => Route::getRoutes()->getByName('orders.id'));

    expect((new App('Handpicked'))->title)->toBe('Handpicked');
});

test('a page without a route falls back to the application name', function (): void {
    Request::setRouteResolver(fn () => null);

    expect((new App())->title)->toBe(config('app.name'));
});

test('a route that was never bound to a request still yields a title', function (): void {
    $route = Route::get('/page-title-unbound/{id}', fn () => '')
        ->name('page-title-unbound')
        ->metadata(['title' => 'Order', 'model' => 'order']);

    expect(PageTitle::forRoute($route))->toBe(__('Order') . ' / ' . config('app.name'));
});

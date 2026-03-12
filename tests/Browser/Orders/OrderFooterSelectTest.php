<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\Product;

test('product select dropdown in footer is not obscured on mobile', function (): void {
    OrderType::query()->delete();

    $orderType = OrderType::factory()
        ->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
            'is_hidden' => false,
        ]);

    $address = Address::factory()
        ->for(
            Contact::factory()
                ->state([
                    'tenant_id' => $this->dbTenant->getKey(),
                ])
        )
        ->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'company' => 'Test Company ' . uniqid(),
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'is_main_address' => true,
            'is_delivery_address' => true,
            'is_invoice_address' => true,
        ]);

    Product::factory()->create([
        'name' => 'Unique Test Product ' . uniqid(),
        'product_number' => 'TEST-ZINDEX-001',
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $address->contact_id,
    ]);

    $page = visit(route('orders.id', ['id' => $order->getKey()]))->on()->mobile();

    $page->assertNoSmoke();

    // Wait for Livewire/Alpine to initialize
    $page->script('() => new Promise(r => setTimeout(r, 2000))');

    // Scroll to the sticky footer and open the product select
    $page->script(<<<'JS'
        () => {
            const footer = document.querySelector('.sticky.bottom-6');
            if (footer) footer.scrollIntoView({ block: 'center' });
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 500))');

    // Open the product select dropdown
    $page->click($this->tsSelect('orderPosition.product_id'));
    $page->script('() => new Promise(r => setTimeout(r, 500))');

    // Type in search to trigger results
    $page->script(<<<'JS'
        () => {
            const input = document.querySelector('[x-ref="search"]') ||
                          document.querySelector('input[placeholder*="search" i]') ||
                          document.querySelector('input[type="search"]');
            if (input) {
                input.value = 'Test';
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1500))');

    // Check if the dropdown options are visually on top (not obscured)
    $result = $page->script(<<<'JS'
        () => {
            const option = document.querySelector('li[role="option"]');
            if (!option) return 'no-option-found';

            const rect = option.getBoundingClientRect();
            const centerX = rect.x + rect.width / 2;
            const centerY = rect.y + rect.height / 2;
            const topEl = document.elementFromPoint(centerX, centerY);

            if (!topEl) return 'no-element-at-point';
            if (option.contains(topEl) || topEl === option) return 'visible';

            return 'obscured';
        }
    JS);

    expect($result)->toBe('visible');
});

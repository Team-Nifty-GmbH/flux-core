<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Comment;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Http\UploadedFile;

beforeEach(function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order, 'is_active' => true]);
    $paymentType = PaymentType::factory()->hasAttached($this->dbTenant, relationship: 'tenants')->create();
    $priceList = PriceList::factory()->create();
    $currency = Currency::factory()->create();

    $this->order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $currency->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);
});

test('comments section loads without js errors', function (): void {
    visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke();
});

test('comment editor initializes with tiptap', function (): void {
    $page = visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke();

    clickTab($page, 'Comments', 'Kommentare');

    $page->assertScript('!!document.querySelector(".ProseMirror, [contenteditable=\\"true\\"]")');
});

test('comment attachment preview opens the lightbox', function (): void {
    // The preview button only renders once the preview conversion exists; run
    // conversions synchronously as no queue worker is available in tests.
    config(['media-library.queue_conversions_by_default' => false]);

    $comment = Comment::factory()->create([
        'model_type' => morph_alias(Order::class),
        'model_id' => $this->order->getKey(),
        'is_internal' => false,
    ]);
    $comment->addMedia(UploadedFile::fake()->image('attachment.png'))
        ->toMediaCollection();

    $page = visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke();

    waitForElement($page, '[data-tab-name="order.comments"]');
    $page->click('[data-tab-name="order.comments"]');

    $page->script(<<<'JS'
        () => {
            window.__lightboxOpened = false;
            window.addEventListener('nuxbe:lightbox:open', () => {
                window.__lightboxOpened = true;
            });
        }
    JS);

    waitForElement($page, '[data-testid="comment-attachment-preview"]', 10000);

    $page->click('[data-testid="comment-attachment-preview"]');

    $page->wait(0.5)
        ->assertScript('window.__lightboxOpened === true')
        ->assertNoJavascriptErrors();
});

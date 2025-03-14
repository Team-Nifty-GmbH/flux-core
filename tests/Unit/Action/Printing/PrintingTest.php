<?php

namespace FluxErp\Tests\Unit\Action\Printing;

use FluxErp\Actions\Printing;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Feature\BaseSetup;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

class PrintingTest extends BaseSetup
{
    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $address = Address::factory()->create([
            'company' => Str::uuid()->toString(),
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->id,
        ]);

        $priceList = PriceList::factory()->create();

        $currency = Currency::factory()->create([
            'is_default' => true,
        ]);

        $language = Language::factory()->create();

        $orderType = OrderType::factory()
            ->create([
                'print_layouts' => ['offer', 'invoice'],
                'client_id' => $this->dbClient->getKey(),
                'order_type_enum' => OrderTypeEnum::Order,
            ]);

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create([
                'is_default' => false,
            ]);

        $this->order = Order::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'address_invoice_id' => $address->id,
            'address_delivery_id' => $address->id,
            'is_locked' => false,
        ]);
    }

    public function test_can_render_html_preview(): void
    {
        $this->withoutVite();

        $result = Printing::make([
            'model_type' => $this->order->getMorphClass(),
            'model_id' => $this->order->id,
            'view' => 'offer',
            'preview' => false,
            'html' => true,
        ])
            ->validate()
            ->execute();

        $this->assertInstanceOf(Htmlable::class, $result);
        $html = $result->toHtml();

        $this->assertStringContainsString(data_get($this->order->address_invoice, 'company'), $html);
        $this->assertStringContainsString('Offer ' . $this->order->order_number, $html);
        $this->assertStringContainsString('Sum net', $html);
        $this->assertStringContainsString('Total Gross', $html);
    }

    public function test_can_render_pdf_preview(): void
    {
        $this->withoutVite();

        $result = Printing::make([
            'model_type' => $this->order->getMorphClass(),
            'model_id' => $this->order->id,
            'view' => 'offer',
            'preview' => true,
            'html' => false,
        ])
            ->validate()
            ->execute();

        $this->assertInstanceOf(PrintableView::class, $result);
        $this->assertNotEmpty($result->pdf);

        $pdf = $result->pdf->output();

        $this->assertNotEmpty($pdf);
        $this->assertStringStartsWith('%PDF-', $pdf);

        $this->assertStringContainsString('%%EOF', $pdf);

        $this->assertStringContainsString('/Pages', $pdf);
        $this->assertStringContainsString('/Type /Page', $pdf);
        $this->assertStringContainsString('/Contents', $pdf);

        $this->assertGreaterThan(1000, strlen($pdf));

    }
}

<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Jobs\CreateDocumentsJob;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

test('deduplicates preview filenames in zip', function (): void {
    Storage::fake();

    $job = app(CreateDocumentsJob::class, [
        'items' => [],
        'selectedPrintLayouts' => ['preview' => ['invoice']],
        'userMorph' => 'user:' . $this->user->getKey(),
    ]);

    $previewOutputs = [];
    for ($i = 1; $i <= 3; $i++) {
        $previewOutputs[] = [
            'output' => 'PDF content ' . $i,
            'file_name' => 'Rechnung Vorschau.pdf',
        ];
    }

    $reflection = new ReflectionMethod($job, 'storeFiles');
    $filePath = $reflection->invoke($job, $previewOutputs, []);

    $zipContent = Storage::get($filePath);
    $tmpZip = tempnam(sys_get_temp_dir(), 'test-') . '.zip';
    file_put_contents($tmpZip, $zipContent);

    $zip = new ZipArchive();
    $zip->open($tmpZip);

    expect($zip->numFiles)->toBe(3)
        ->and($zip->getNameIndex(0))->toBe('Rechnung Vorschau.pdf')
        ->and($zip->getNameIndex(1))->toBe('Rechnung Vorschau (1).pdf')
        ->and($zip->getNameIndex(2))->toBe('Rechnung Vorschau (2).pdf')
        ->and($zip->getFromIndex(0))->toBe('PDF content 1')
        ->and($zip->getFromIndex(1))->toBe('PDF content 2')
        ->and($zip->getFromIndex(2))->toBe('PDF content 3');

    $zip->close();
    unlink($tmpZip);
});

test('keeps unique filenames unchanged in zip', function (): void {
    Storage::fake();

    $job = app(CreateDocumentsJob::class, [
        'items' => [],
        'selectedPrintLayouts' => ['preview' => ['invoice']],
        'userMorph' => 'user:' . $this->user->getKey(),
    ]);

    $previewOutputs = [
        ['output' => 'PDF A', 'file_name' => 'Rechnung RE-001.pdf'],
        ['output' => 'PDF B', 'file_name' => 'Rechnung RE-002.pdf'],
        ['output' => 'PDF C', 'file_name' => 'Rechnung RE-003.pdf'],
    ];

    $reflection = new ReflectionMethod($job, 'storeFiles');
    $filePath = $reflection->invoke($job, $previewOutputs, []);

    $zipContent = Storage::get($filePath);
    $tmpZip = tempnam(sys_get_temp_dir(), 'test-') . '.zip';
    file_put_contents($tmpZip, $zipContent);

    $zip = new ZipArchive();
    $zip->open($tmpZip);

    expect($zip->numFiles)->toBe(3)
        ->and($zip->getNameIndex(0))->toBe('Rechnung RE-001.pdf')
        ->and($zip->getNameIndex(1))->toBe('Rechnung RE-002.pdf')
        ->and($zip->getNameIndex(2))->toBe('Rechnung RE-003.pdf');

    $zip->close();
    unlink($tmpZip);
});

test('invoice of an order whose contact was deleted fails validation and assigns no number', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);

    $order = Order::factory()
        ->for(Currency::factory())
        ->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
            'order_type_id' => OrderType::factory()
                ->create([
                    'order_type_enum' => OrderTypeEnum::Order,
                    'print_layouts' => ['invoice'],
                ])
                ->getKey(),
            'payment_type_id' => PaymentType::factory()
                ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
                ->create()
                ->getKey(),
            'price_list_id' => PriceList::factory()->create()->getKey(),
            'contact_id' => $contact->getKey(),
            'address_invoice_id' => $address->getKey(),
            'address_delivery_id' => $address->getKey(),
            'invoice_number' => null,
            'is_locked' => false,
        ]);

    $contact->delete();

    $job = app(CreateDocumentsJob::class, [
        'items' => [
            [
                'model_type' => $order->getMorphClass(),
                'model_id' => $order->getKey(),
                'layouts' => ['invoice'],
            ],
        ],
        'selectedPrintLayouts' => ['download' => ['invoice']],
        'userMorph' => 'user:' . $this->user->getKey(),
    ]);

    try {
        $job->throwException()->handle();
        $this->fail('Expected a validation error for the deleted contact.');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('contact_id');
    }

    expect($order->refresh()->invoice_number)->toBeNull();
});

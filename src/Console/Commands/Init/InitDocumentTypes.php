<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\Client;
use FluxErp\Models\DocumentType;
use Illuminate\Console\Command;

class InitDocumentTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:document-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiates Document Types and fills table with data.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $documentTypes = [
            (object) [
                'name' => 'Offer',
                'description' => '',
                'additional_header' => '',
                'additional_footer' => '',
                'is_active' => 1,
            ],
            (object) [
                'name' => 'Order',
                'description' => '',
                'additional_header' => '',
                'additional_footer' => '',
                'is_active' => 1,
            ],
            (object) [
                'name' => 'Invoice',
                'description' => '',
                'additional_header' => '',
                'additional_footer' => '',
                'is_active' => 1,
            ],
            (object) [
                'name' => 'Credit',
                'description' => '',
                'additional_header' => '',
                'additional_footer' => '',
                'is_active' => 1,
            ],
            (object) [
                'name' => 'Incoming invoice',
                'description' => '',
                'additional_header' => '',
                'additional_footer' => '',
                'is_active' => 1,
            ],
            (object) [
                'name' => 'Incoming credit',
                'description' => '',
                'additional_header' => '',
                'additional_footer' => '',
                'is_active' => 1,
            ],
        ];

        foreach ($documentTypes as $documentType) {
            $clientId = Client::query()
                ->first()
                ?->id;

            if ($clientId) {
                DocumentType::query()
                    ->updateOrCreate([
                        'name' => $documentType->name,
                    ], [
                        'client_id' => $clientId,
                        'description' => $documentType->description,
                        'additional_header' => $documentType->additional_header,
                        'additional_footer' => $documentType->additional_footer,
                        'is_active' => $documentType->is_active,
                    ]);
            }
        }

        $this->info('Document Types initiated!');
    }
}

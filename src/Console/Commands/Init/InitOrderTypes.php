<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\Client;
use FluxErp\Models\OrderType;
use Illuminate\Console\Command;

class InitOrderTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:order-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiates Order Types and fills table with data.';

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
        $orderTypes = [
            (object) [
                'name' => 'Offer',
                'description' => '',
                'is_active' => 1,
                'is_hidden' => 0,
            ],
            (object) [
                'name' => 'Order',
                'description' => '',
                'is_active' => 1,
                'is_hidden' => 0,
            ],
            (object) [
                'name' => 'Invoice',
                'description' => '',
                'is_active' => 1,
                'is_hidden' => 0,
            ],
            (object) [
                'name' => 'Credit',
                'description' => '',
                'is_active' => 1,
                'is_hidden' => 0,
            ],
            (object) [
                'name' => 'Incoming invoice',
                'description' => '',
                'is_active' => 1,
                'is_hidden' => 0,
            ],
            (object) [
                'name' => 'Incoming credit',
                'description' => '',
                'is_active' => 1,
                'is_hidden' => 0,
            ],
        ];

        foreach ($orderTypes as $orderType) {
            $clientId = Client::query()
                ->first()
                ?->id;

            if ($clientId) {
                OrderType::query()
                    ->updateOrCreate([
                        'name' => $orderType->name,
                    ], [
                        'client_id' => $clientId,
                        'description' => $orderType->description,
                        'is_active' => $orderType->is_active,
                        'is_hidden' => $orderType->is_hidden,
                    ]);
            }
        }

        $this->info('Order Types initiated!');
    }
}

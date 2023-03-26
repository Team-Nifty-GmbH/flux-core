<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use Illuminate\Console\Command;

class InitPaymentTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:payment-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiates Payment Types and fills table with data.';

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
        $paymentTypes = [
            (object) [
                'name' => 'Invoice',
                'description' => 'Payment after receiving the invoice.',
                'payment_reminder_days_1' => '',
                'payment_reminder_days_2' => '',
                'payment_reminder_days_3' => '',
                'payment_target' => '',
                'payment_discount_target' => '',
                'payment_discount_percentage' => '',
                'is_active' => 1,
            ],
            (object) [
                'name' => 'Cash',
                'description' => 'Payment in cash.',
                'payment_reminder_days_1' => '',
                'payment_reminder_days_2' => '',
                'payment_reminder_days_3' => '',
                'payment_target' => '',
                'payment_discount_target' => '',
                'payment_discount_percentage' => '',
                'is_active' => 1,
            ],
            (object) [
                'name' => 'In advance',
                'description' => 'Payment in advance.',
                'payment_reminder_days_1' => '',
                'payment_reminder_days_2' => '',
                'payment_reminder_days_3' => '',
                'payment_target' => '',
                'payment_discount_target' => '',
                'payment_discount_percentage' => '',
                'is_active' => 1,
            ],
            (object) [
                'name' => 'On delivery',
                'description' => 'Payment in cash on delivery.',
                'payment_reminder_days_1' => '',
                'payment_reminder_days_2' => '',
                'payment_reminder_days_3' => '',
                'payment_target' => '',
                'payment_discount_target' => '',
                'payment_discount_percentage' => '',
                'is_active' => 1,
            ],
            (object) [
                'name' => 'Debit direct',
                'description' => 'Debit direct payment.',
                'payment_reminder_days_1' => '',
                'payment_reminder_days_2' => '',
                'payment_reminder_days_3' => '',
                'payment_target' => '',
                'payment_discount_target' => '',
                'payment_discount_percentage' => '',
                'is_active' => 1,
            ],
        ];

        foreach ($paymentTypes as $paymentType) {
            $clientId = Client::query()
                ->first()
                ?->id;

            if ($clientId) {
                PaymentType::query()
                    ->updateOrCreate([
                        'name' => $paymentType->name,
                    ], [
                        'client_id' => $clientId,
                        'description' => $paymentType->description,
                        'payment_reminder_days_1' => $paymentType->payment_reminder_days_1,
                        'payment_reminder_days_2' => $paymentType->payment_reminder_days_2,
                        'payment_reminder_days_3' => $paymentType->payment_reminder_days_3,
                        'payment_target' => $paymentType->payment_target,
                        'payment_discount_target' => $paymentType->payment_discount_target,
                        'payment_discount_percentage' => $paymentType->payment_discount_percentage,
                        'is_active' => $paymentType->is_active,
                    ]);
            }
        }

        $this->info('Payment Types initiated!');
    }
}

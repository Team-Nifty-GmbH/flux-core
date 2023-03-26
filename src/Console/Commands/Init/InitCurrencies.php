<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Services\CurrencyService;
use Illuminate\Console\Command;

class InitCurrencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:currencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiates Currencies and fills table with data.';

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
        $currencyController = new CurrencyService();
        $currencyController->initializeCurrencies();

        $this->info('Currencies initiated!');
    }
}

<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\Currency;
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
        $path = resource_path() . '/init-files/currencies.json';
        if (! file_exists($path)) {
            return;
        }

        $json = json_decode(file_get_contents($path));

        if ($json->model === 'Currency') {
            $jsonCurrencies = $json->data;

            if ($jsonCurrencies) {
                $isDefault = false;
                foreach ($jsonCurrencies as $jsonCurrency) {
                    // Save to database.
                    $isDefault = $isDefault ? false : $jsonCurrency->is_default;
                    Currency::query()
                        ->updateOrCreate([
                            'iso' => $jsonCurrency->iso,
                        ], [
                            'name' => $jsonCurrency->name,
                            'symbol' => $jsonCurrency->symbol,
                            'is_default' => $isDefault,
                        ]);
                }
            }

            if (! Currency::query()->where('is_default')->exists()) {
                Currency::query()
                    ->first()
                    ->update(['is_default' => false]);
            }
        }

        $this->info('Currencies initiated!');
    }
}

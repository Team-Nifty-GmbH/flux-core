<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\Currency;
use Illuminate\Console\Command;

class InitCurrencies extends Command
{
    protected $signature = 'init:currencies';

    protected $description = 'Initiates Currencies and fills table with data.';

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
                    app(Currency::class)->query()
                        ->updateOrCreate([
                            'iso' => $jsonCurrency->iso,
                        ], [
                            'name' => $jsonCurrency->name,
                            'symbol' => $jsonCurrency->symbol,
                            'is_default' => $isDefault,
                        ]);
                }
            }

            if (! app(Currency::class)->query()->where('is_default')->exists()) {
                app(Currency::class)->query()
                    ->first()
                    ->update(['is_default' => true]);
            }
        }

        $this->info('Currencies initiated!');
    }
}

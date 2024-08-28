<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\AddressType;
use FluxErp\Models\Client;
use Illuminate\Console\Command;

class InitAddressTypes extends Command
{
    protected $signature = 'init:address-types';

    protected $description = 'Initializes a default set of address types.';

    public function handle(): void
    {
        $path = resource_path() . '/init-files/address-types.json';
        if (! file_exists($path)) {
            return;
        }

        $json = json_decode(file_get_contents($path), true);

        if ($json['model'] === 'AddressType') {
            $jsonAddressTypes = $json['data'];

            if ($jsonAddressTypes) {
                foreach (app(Client::class)->all() as $client) {
                    foreach ($jsonAddressTypes as $jsonAddressType) {
                        $data = array_map(function ($value) {
                            return __($value);
                        }, $jsonAddressType);
                        $data['client_id'] = $client->id;

                        // Gather necessary foreign keys.
                        $addressType = resolve_static(AddressType::class, 'query')
                            ->where('address_type_code', $data['address_type_code'])
                            ->where('client_id', $client->id)
                            ->firstOrNew();

                        if (! $addressType->exists) {
                            $addressType->fill($data);
                            $addressType->save();
                        }
                    }
                }
            }
        }

        $this->info('Address Types initiated!');
    }
}

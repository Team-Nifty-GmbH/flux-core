<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\AddressType;
use FluxErp\Models\Tenant;
use Illuminate\Console\Command;

class InitAddressTypes extends Command
{
    protected $description = 'Initializes a default set of address types.';

    protected $signature = 'init:address-types';

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
                foreach (app(Tenant::class)->all() as $tenant) {
                    foreach ($jsonAddressTypes as $jsonAddressType) {
                        $data = array_map(function ($value) {
                            return __($value);
                        }, $jsonAddressType);
                        $data['tenant_id'] = $tenant->id;

                        // Gather necessary foreign keys.
                        $addressType = resolve_static(AddressType::class, 'query')
                            ->where('address_type_code', $data['address_type_code'])
                            ->where('tenant_id', $tenant->id)
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

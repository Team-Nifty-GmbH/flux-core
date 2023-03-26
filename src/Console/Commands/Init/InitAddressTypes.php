<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Services\AddressTypeService;
use Illuminate\Console\Command;

class InitAddressTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:address-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes a default set of address types.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        (new AddressTypeService())->initialize();

        $this->info('Address Types initiated!');

        return 0;
    }
}

<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\Client;
use Illuminate\Console\Command;

class InitClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:clients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the clients for a new installation';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $attributes = ['id', 'name'];
        $clients = Client::all()
            ->map(function (Client $client) use ($attributes) {
                return $client->only($attributes);
            });

        $this->table(
            $attributes,
            $clients->toArray()
        );

        while (true) {
            if (! $this->confirm(__('Add a new client?'))) {
                break;
            }

            $client = new Client();
            $client->name = $this->ask(__('Enter client name'));

            while (true) {
                $client->client_code = strtoupper($this->ask(__('Enter client code (short form)')));
                if (! Client::query()->where('client_code', $client->client_code)->first()) {
                    break;
                } else {
                    $this->warn(__('The client code has to be unique, your given code already exists!'));
                }
            }

            $client->ceo = ucwords($this->ask(__('Enter CEO name(s)')));
            $client->street = ucwords($this->ask(__('Enter street')));
            $client->city = ucwords($this->ask(__('Enter city')));
            $client->postcode = $this->ask(__('Enter zip code'));
            $client->phone = $this->ask(__('Enter phone number'));
            $client->fax = $this->ask(__('Enter fax number'));
            $client->website = $this->ask(__('Enter website url'));
            $client->email = $this->ask(__('Enter email address'));

            if ($this->confirm(__('Add a bank connection?'))) {
                $client->bank_name = ucwords($this->ask(__('Enter bank name')));
                $client->bank_code = $this->ask(__('Enter bank code'));
                $client->bank_iban = $this->ask(__('Enter iban'));
                $client->bank_swift = $this->ask(__('Enter swift code'));
                $client->bank_bic = strtoupper($this->ask(__('Enter bic code')));
            }

            $client->save();
            $this->info('<bg=green>Client created</>');

            $clients = Client::all()
                ->map(function (Client $client) use ($attributes) {
                    return $client->only($attributes);
                });

            $this->table(
                $attributes,
                $clients->toArray()
            );
        }

        $this->info('Clients initiated!');
    }
}

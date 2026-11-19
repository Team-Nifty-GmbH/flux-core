<?php

namespace FluxErp\Console\Commands;

use FluxErp\Models\Contact;
use FluxErp\Models\VatRate;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use function Laravel\Prompts\info;
use function Laravel\Prompts\table;

class SyncCountryTaxExemptionsCommand extends Command
{
    protected $description = 'Set the tax exemption of every contact from the country of its delivery address.';

    protected $signature = 'flux:sync-country-tax-exemptions {--dry-run : Only list the contacts that would change}';

    public function handle(): int
    {
        $vatRates = resolve_static(VatRate::class, 'query')
            ->pluck('name', 'id');
        $rows = [];

        resolve_static(Contact::class, 'query')
            ->chunkById(500, function (Collection $contacts) use ($vatRates, &$rows): void {
                foreach ($contacts as $contact) {
                    $taxExemptionId = $contact->getCountryTaxExemptionId();

                    if ($taxExemptionId === $contact->vat_rate_id) {
                        continue;
                    }

                    $rows[] = [
                        $contact->getKey(),
                        $contact->customer_number,
                        $vatRates->get($contact->vat_rate_id) ?? '-',
                        $vatRates->get($taxExemptionId) ?? '-',
                    ];

                    if (! $this->option('dry-run')) {
                        $contact->vat_rate_id = $taxExemptionId;
                        $contact->save();
                    }
                }
            });

        if ($rows) {
            table(['ID', 'Customer number', 'Current', 'New'], $rows);
        }

        info(
            ($this->option('dry-run') ? 'Contacts that would change: ' : 'Contacts changed: ')
            . count($rows)
        );

        return self::SUCCESS;
    }
}

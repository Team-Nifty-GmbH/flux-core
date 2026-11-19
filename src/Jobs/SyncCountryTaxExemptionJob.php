<?php

namespace FluxErp\Jobs;

use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;

class SyncCountryTaxExemptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public int $countryId, public ?int $previousTaxExemptionId = null) {}

    public function handle(): void
    {
        $taxExemptionId = resolve_static(Country::class, 'query')
            ->whereKey($this->countryId)
            ->value('tax_exemption_id');
        $inCountry = fn (Builder $query) => $query->where('country_id', $this->countryId);

        resolve_static(Contact::class, 'query')
            ->where(fn (Builder $query) => $query
                ->whereHas('deliveryAddress', $inCountry)
                ->orWhere(fn (Builder $query) => $query
                    ->whereNull('delivery_address_id')
                    ->whereHas('mainAddress', $inCountry)
                )
                ->when($taxExemptionId, fn (Builder $query) => $query->orWhere('vat_rate_id', $taxExemptionId))
            )
            ->chunkById(100, fn (Collection $contacts) => $contacts->each(function (Contact $contact): void {
                // The former rate of this country was no manual choice of the contact.
                if ($this->previousTaxExemptionId && $contact->vat_rate_id === $this->previousTaxExemptionId) {
                    $contact->vat_rate_id = null;
                }

                $contact->syncCountryTaxExemption();
            }));
    }
}

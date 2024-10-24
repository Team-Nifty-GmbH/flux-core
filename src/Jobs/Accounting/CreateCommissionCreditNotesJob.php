<?php

namespace FluxErp\Jobs\Accounting;

use FluxErp\Actions\Commission\CreateCommissionCreditNotes;
use FluxErp\Contracts\ShouldBeMonitored;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class CreateCommissionCreditNotesJob implements ShouldBeMonitored, ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public array $commissionIds) {}

    public function handle(): void
    {
        CreateCommissionCreditNotes::make($this->commissionIds)
            ->validate()
            ->execute();
    }

    public function uniqueId(): string
    {
        return md5(serialize($this->commissionIds));
    }
}

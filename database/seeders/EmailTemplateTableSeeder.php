<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Seeder;

class EmailTemplateTableSeeder extends Seeder
{
    public function run(): void
    {
        collect(Relation::morphMap())
            ->filter(fn (string $modelClass) => is_a(
                resolve_static($modelClass, 'class'),
                OffersPrinting::class,
                true)
            )
            ->each(fn (string $modelClass, string $modelType) => EmailTemplate::factory()
                ->create([
                    'model_type' => $modelType,
                ])
            );
    }
}

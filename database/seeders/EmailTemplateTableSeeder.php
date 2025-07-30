<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Seeder;

class EmailTemplateTableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_keys(Relation::morphMap()) as $modelType) {
            EmailTemplate::factory()->create([
                'model_type' => $modelType,
            ]);
        }
    }
}

<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderSection;
use Illuminate\Database\Seeder;

class FormBuilderSectionTableSeeder extends Seeder
{
    public function run(): void
    {
        $formBuilderForms = FormBuilderForm::all();
        foreach ($formBuilderForms as $formBuilderForm) {
            FormBuilderSection::factory()
                ->count(rand(0, 5))
                ->create([
                    'form_id' => $formBuilderForm->id,
                ]);
        }
    }
}

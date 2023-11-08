<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderSection;
use Illuminate\Database\Seeder;

class FormBuilderFieldTableSeeder extends Seeder
{
    public function run(): void
    {
        $sections = FormBuilderSection::all();
        $types = [
            'text',
            'textarea',
            'select',
            'checkbox',
            'radio',
            'date',
            'time',
            'datetime',
            'number',
            'email',
            'password',
            'range',
        ];

        foreach ($sections as $section) {
            foreach ($types as $type) {
                FormBuilderField::factory()
                    ->count(rand(0, 2))
                    ->create([
                        'section_id' => $section->id,
                        'type' => $type,
                    ]);
            }
        }
    }
}

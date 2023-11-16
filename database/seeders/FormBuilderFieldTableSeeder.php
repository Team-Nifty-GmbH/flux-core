<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Enums\FormBuilderTypeEnum;
use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderSection;
use Illuminate\Database\Seeder;

class FormBuilderFieldTableSeeder extends Seeder
{
    public function run(): void
    {
        $sections = FormBuilderSection::all();
        $types = FormBuilderTypeEnum::values();

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

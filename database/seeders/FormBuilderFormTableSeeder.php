<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class FormBuilderFormTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        FormBuilderForm::factory()->count(5)->create(
            [
                'user_id' => $users->random(),
            ]
        );
    }
}

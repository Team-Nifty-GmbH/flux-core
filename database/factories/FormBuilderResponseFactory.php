<?php

namespace FluxErp\Database\Factories;

use App\Models\User;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderResponse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FormBuilderResponseFactory extends Factory
{
    protected $model = FormBuilderResponse::class;

    public function definition(): array
    {
        $user = User::all();
        $form = FormBuilderForm::all();

        return [
            'user_id' => $user->random(),
            'form_id' => $form->random(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}

<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Presentation;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use Illuminate\Database\Eloquent\Model;

class CreatePresentationRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new Presentation())->hasAdditionalColumnsValidationRules(),
            [
                'uuid' => 'string|uuid|unique:presentations,uuid',
                'name' => 'required|string',
                'notice' => 'sometimes|string|nullable',
                'model_type' => [
                    'required_with:model_id',
                    'string',
                    new ClassExists(instanceOf: Model::class),
                ],
                'model_id' => [
                    'required_with:model_type',
                    'integer',
                    new MorphExists(),
                ],
                'is_public' => 'boolean',
            ],
        );
    }
}

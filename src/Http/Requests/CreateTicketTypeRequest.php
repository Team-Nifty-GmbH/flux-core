<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use Illuminate\Database\Eloquent\Model;

class CreateTicketTypeRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'model_type' => [
                'string',
                'nullable',
                new ClassExists(instanceOf: Model::class),
            ],
        ];
    }
}

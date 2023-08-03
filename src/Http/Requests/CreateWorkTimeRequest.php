<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class CreateWorkTimeRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->user()->id,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'work_time_type_id' => 'nullable|integer|exists:work_time_types,id,deleted_at,NULL',
            'trackable_type' => [
                'required_with:trackable_id',
                'string',
                new ClassExists(uses: Trackable::class, instanceOf: Model::class),
            ],
            'trackable_id' => [
                'required_with:trackable_type',
                'integer',
                new MorphExists('trackable_type'),
            ],
            'started_at' => 'required_with:ended_at|nullable|date_format:Y-m-d H:i:s|before:now',
            'ended_at' => 'nullable|date_format:Y-m-d H:i:s|after:started_at',
            'description' => 'string|nullable',
            'is_pause' => 'boolean',
        ];
    }
}

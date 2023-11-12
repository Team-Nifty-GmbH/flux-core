<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Lockable;
use Illuminate\Database\Eloquent\Model;

class LockRecordRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'model_type' => [
                'required',
                'string',
                new ClassExists(uses: Lockable::class, instanceOf: Model::class),
            ],
            'model_id' => [
                'required',
                'integer',
                new MorphExists(),
            ],
        ];
    }
}

<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\Rules\Enum;

class CreateOrderTypeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:order_types,uuid',
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
            'name' => 'required|string',
            'description' => 'string|nullable',
            'mail_subject' => 'string|nullable',
            'mail_body' => 'string|nullable',
            'print_layouts' => 'array|nullable',
            'print_layouts.*' => 'required|string',
            'order_type_enum' => [
                'required',
                new Enum(OrderTypeEnum::class),
            ],
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
        ];
    }
}

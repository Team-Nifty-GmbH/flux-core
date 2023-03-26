<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\HasCalendarEvents;
use Illuminate\Database\Eloquent\Model;

class CreateCalendarEventRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'calendar_id' => 'required_without_all:model_type,model_id|integer|exists:calendars,id',
            'model_type' => [
                'required_without:calendar_id',
                'string',
                new ClassExists(HasCalendarEvents::class, Model::class),
            ],
            'model_id' => [
                'required_without:calendar_id',
                'integer',
                new MorphExists(),
            ],
            'title' => 'required|string',
            'subtitle' => 'string|nullable',
            'description' => 'string|nullable',
            'starts_at' => 'required|date_format:Y-m-d H:i',
            'ends_at' => 'required|date_format:Y-m-d H:i|after_or_equal:starts_at',
            'is_all_day' => 'boolean',
            'invited_addresses' => 'array',
            'invited_addresses.*.id' => 'sometimes|exists:addresses,id,deleted_at,NULL',
            'invited_addresses.*.status' => 'sometimes|string|in:accepted,declined,maybe|nullable',
            'invited_users' => 'array',
            'invited_users.*.id' => 'sometimes|exists:users,id,deleted_at,NULL',
            'invited_users.*.status' => 'sometimes|string|in:accepted,declined,maybe|nullable',
        ];
    }
}

<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\HasCalendarEvents;
use Illuminate\Database\Eloquent\Model;

class UpdateCalendarEventRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:calendar_events,id',
            'calendar_id' => 'sometimes|required|integer|exists:calendars,id',
            'model_type' => [
                'sometimes',
                'string',
                new ClassExists(HasCalendarEvents::class, Model::class),
                'nullable',
            ],
            'model_id' => [
                'sometimes',
                'integer',
                new MorphExists(),
                'nullable',
            ],
            'title' => 'sometimes|required|string',
            'subtitle' => 'string|nullable',
            'description' => 'string|nullable',
            'starts_at' => 'sometimes|required|date_format:Y-m-d H:i',
            'ends_at' => 'sometimes|required|date_format:Y-m-d H:i|after_or_equal:starts_at',
            'is_all_day' => 'boolean',
            'invited_addresses' => 'array',
            'invited_addresses.*.id' => 'sometimes|exists:addresses,id',
            'invited_addresses.*.status' => 'sometimes|string|in:accepted,declined,maybe|nullable',
            'invited_users' => 'array',
            'invited_users.*.id' => 'sometimes|exists:users,id',
            'invited_users.*.status' => 'sometimes|string|in:accepted,declined,maybe|nullable',
        ];
    }
}

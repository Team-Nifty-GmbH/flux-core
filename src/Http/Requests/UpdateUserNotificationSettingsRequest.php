<?php

namespace FluxErp\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateUserNotificationSettingsRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notification_type' => [
                'required',
                'string',
                Rule::in(data_get(config('notifications.model_notifications'), '*.*')),
            ],
            'channel' => [
                'required',
                'string',
                Rule::in(array_keys(config('notifications.channels'))),
            ],
            'is_active' => 'required|boolean',
            'is_anonymous' => 'sometimes|required|boolean|declined',
        ];
    }
}

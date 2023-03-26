<?php

namespace FluxErp\Http\Requests;

use FluxErp\Http\Livewire\Features\Calendar\Calendar;
use FluxErp\Rules\ClassExists;
use Illuminate\View\Component;

class CreateCalendarRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parent_id' => 'integer|nullable|exists:calendars,id',
            'user_id' => 'integer|nullable|exists:users,id,deleted_at,NULL',
            'name' => 'required|string',
            'module' => [
                'string',
                new ClassExists(instanceOf: Calendar::class),
                'nullable',
            ],
            'color' => [
                'string',
                'regex:/^(\#[\da-f]{3}|\#[\da-f]{6}|rgba\(((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*,\s*){2}((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*)(,\s*(0\.\d+|1))\)|hsla\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)(,\s*(0\.\d+|1))\)|rgb\(((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*,\s*){2}((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*)|hsl\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)\))$/i',
            ],
            'event_component' => [
                new ClassExists(instanceOf: Component::class),
                'nullable',
            ],
            'is_public' => 'boolean',
        ];
    }
}

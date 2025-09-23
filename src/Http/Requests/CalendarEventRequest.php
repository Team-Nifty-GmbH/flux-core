<?php

namespace FluxErp\Http\Requests;

class CalendarEventRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'info' => 'required|array',
            'info.start' => 'required|date',
            'info.end' => 'required|date',
            'info.startStr' => 'required|date',
            'info.endStr' => 'required|date',

            'calendar' => 'required|array',
            'calendar.id' => 'required',
            'calendar.hasNoEvents' => 'nullable|boolean',
            'calendar.modelType' => 'nullable|string',
            'calendar.isVirtual' => 'nullable|boolean',
            'calendar.permission' => 'nullable|string|in:owner,editor,reader',

            'componentSnapshot' => 'required|array',
        ];
    }
}

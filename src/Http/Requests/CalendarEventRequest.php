<?php

namespace FluxErp\Http\Requests;

class CalendarEventRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'info' => 'required|array',
            'info.start' => 'required|string',
            'info.end' => 'required|string',
            'info.startStr' => 'required|string',
            'info.endStr' => 'required|string',
            'info.timeZone' => 'required|string',

            'calendar' => 'required|array',
            'calendar.id' => 'required',
            'calendar.name' => 'required|string',
            'calendar.hasNoEvents' => 'nullable|boolean',
            'calendar.modelType' => 'nullable|string',
            'calendar.isVirtual' => 'nullable|boolean',
            'calendar.permission' => 'nullable|string|in:owner,editor,reader',

            'calendar.label' => 'nullable|string',
            'calendar.color' => 'nullable|string',
            'calendar.resourceEditable' => 'nullable|boolean',
            'calendar.hasRepeatableEvents' => 'nullable|boolean',
            'calendar.isPublic' => 'nullable|boolean',
            'calendar.isShared' => 'nullable|boolean',
            'calendar.group' => 'nullable|string',
            'calendar.isLoading' => 'nullable|boolean',
            'calendar.parentId' => 'nullable|string',
            'calendar.children' => 'nullable|array',

            'componentSnapshot' => 'required|array',
            'componentSnapshot.checksum' => 'required|string',
        ];
    }
}

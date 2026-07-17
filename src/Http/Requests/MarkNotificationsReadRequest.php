<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Notification;
use FluxErp\Rules\ModelExists;

class MarkNotificationsReadRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required_without:all',
                'prohibits:all',
                'string',
                app(ModelExists::class, ['model' => Notification::class])
                    ->where('notifiable_type', $this->user()->getMorphClass())
                    ->where('notifiable_id', $this->user()->getKey()),
            ],
            'all' => [
                'required_without:id',
                'boolean',
            ],
        ];
    }
}

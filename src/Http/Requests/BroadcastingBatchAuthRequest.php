<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BroadcastingBatchAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'socket_id' => ['required', 'string'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*.name' => ['required', 'string'],
            'channels.*.socket_id' => ['nullable', 'string'],
        ];
    }
}

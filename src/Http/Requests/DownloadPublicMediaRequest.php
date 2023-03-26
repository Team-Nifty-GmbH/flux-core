<?php

namespace FluxErp\Http\Requests;

class DownloadPublicMediaRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'model_type' => qualify_model($this->model_type),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'model_id' => 'required|integer',
            'model_type' => 'required|string',
            'conversion' => 'sometimes|required|string',
        ];
    }
}

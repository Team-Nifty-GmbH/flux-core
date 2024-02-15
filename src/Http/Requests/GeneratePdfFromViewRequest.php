<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ViewExists;

class GeneratePdfFromViewRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'template_ids' => 'required_without_all:view,data|array',
            'view' => ['required_without:template_ids', 'string', new ViewExists()],
            'data' => 'present|array',
            'preview' => 'boolean',
            'is_template' => 'boolean',
            'template_name' => 'required_if:is_template,true',
            'sort' => 'integer',
            'model_id' => 'integer|required_with:model_type,template_ids',
            'model_type' => 'string|required_with:model_id,template_ids',
            'store' => 'boolean|required_if:store_pdf,true',
            'is_public' => 'boolean|nullable',
            'store_pdf' => 'boolean',
            'store_pdf_public' => 'boolean',
            'margin_top' => 'string',
            'margin_bottom' => 'string',
            'margin_left' => 'string',
            'margin_right' => 'string',
            'paper_width' => 'string',
            'paper_height' => 'string',
        ];
    }
}

<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateDocumentTypeRequest;
use FluxErp\Models\DocumentType;
use Illuminate\Support\Facades\Validator;

class CreateDocumentType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateDocumentTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [DocumentType::class];
    }

    public function execute(): DocumentType
    {
        $documentType = new DocumentType($this->data);
        $documentType->save();

        return $documentType;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new DocumentType());

        $this->data = $validator->validate();

        return $this;
    }
}

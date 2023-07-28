<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateDocumentTypeRequest;
use FluxErp\Models\DocumentType;
use Illuminate\Support\Facades\Validator;

class CreateDocumentType extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateDocumentTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [DocumentType::class];
    }

    public function performAction(): DocumentType
    {
        $documentType = new DocumentType($this->data);
        $documentType->save();

        return $documentType;
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new DocumentType());

        $this->data = $validator->validate();
    }
}

<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateDocumentTypeRequest;
use FluxErp\Models\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateDocumentType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateDocumentTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [DocumentType::class];
    }

    public function execute(): Model
    {
        $documentType = DocumentType::query()
            ->whereKey($this->data['id'])
            ->first();

        $documentType->fill($this->data);
        $documentType->save();

        return $documentType->withoutRelations()->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new DocumentType());

        $this->data = $validator->validate();

        return $this;
    }
}

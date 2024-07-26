<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateDocumentTypeRequest;
use FluxErp\Models\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * @deprecated
 */
class UpdateDocumentType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = app(UpdateDocumentTypeRequest::class)->rules();
    }

    public static function models(): array
    {
        return [DocumentType::class];
    }

    public function performAction(): Model
    {
        $documentType = DocumentType::query()
            ->whereKey($this->data['id'])
            ->first();

        $documentType->fill($this->data);
        $documentType->save();

        return $documentType->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new DocumentType());

        $this->data = $validator->validate();
    }
}

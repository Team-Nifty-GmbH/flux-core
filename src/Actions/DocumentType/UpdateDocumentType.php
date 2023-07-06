<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateDocumentTypeRequest;
use FluxErp\Models\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateDocumentType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateDocumentTypeRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'document type.update';
    }

    public static function description(): string|null
    {
        return 'update document-type';
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

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new DocumentType());

        $this->data = $validator->validate();

        return $this;
    }
}

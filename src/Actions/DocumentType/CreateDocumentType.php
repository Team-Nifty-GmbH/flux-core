<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateDocumentTypeRequest;
use FluxErp\Models\DocumentType;
use Illuminate\Support\Facades\Validator;

class CreateDocumentType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateDocumentTypeRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'document-type.create';
    }

    public static function description(): string|null
    {
        return 'create document type';
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

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}

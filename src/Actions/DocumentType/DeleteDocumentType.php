<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\DocumentType;
use Illuminate\Support\Facades\Validator;

class DeleteDocumentType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:document_types,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'document-type.delete';
    }

    public static function description(): string|null
    {
        return 'delete document type';
    }

    public static function models(): array
    {
        return [DocumentType::class];
    }

    public function execute()
    {
        return DocumentType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
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

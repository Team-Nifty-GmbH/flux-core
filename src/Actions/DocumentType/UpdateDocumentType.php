<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DocumentType;
use FluxErp\Rulesets\DocumentType\UpdateDocumentTypeRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateDocumentType extends FluxAction
{
    public static function models(): array
    {
        return [DocumentType::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateDocumentTypeRuleset::class;
    }

    public function performAction(): Model
    {
        $documentType = resolve_static(DocumentType::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $documentType->fill($this->getData());
        $documentType->save();

        return $documentType->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $documentType = resolve_static(DocumentType::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['is_payroll_record', 'retention_years']);

        $isPayrollRecord = $this->getData('is_payroll_record', $documentType->is_payroll_record);
        $retentionYears = array_key_exists('retention_years', $this->getData())
            ? $this->getData('retention_years')
            : $documentType->retention_years;

        if ($isPayrollRecord && ! $retentionYears) {
            throw ValidationException::withMessages([
                'retention_years' => [__('Payroll records need a retention period.')],
            ]);
        }
    }
}

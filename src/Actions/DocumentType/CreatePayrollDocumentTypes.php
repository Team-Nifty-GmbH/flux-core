<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Employee;
use Illuminate\Support\Collection;

class CreatePayrollDocumentTypes extends FluxAction
{
    public static function models(): array
    {
        return [DocumentType::class];
    }

    public function performAction(): Collection
    {
        $existing = resolve_static(DocumentType::class, 'query')->pluck('name');

        return collect($this->documentTypes())
            ->map(fn (array $documentType): array => array_merge(
                $documentType,
                ['name' => __(data_get($documentType, 'name'))]
            ))
            ->reject(fn (array $documentType): bool => $existing->contains(data_get($documentType, 'name')))
            ->map(fn (array $documentType): DocumentType => CreateDocumentType::make(array_merge(
                [
                    'model_types' => [morph_alias(Employee::class)],
                    'retention_years' => 6,
                    'is_owner_uploadable' => false,
                    'is_payroll_record' => true,
                ],
                $documentType
            ))
                ->validate()
                ->execute()
            )
            ->values();
    }

    protected function documentTypes(): array
    {
        return [
            ['name' => 'Employment contract'],
            ['name' => 'Proof of insurance exemption'],
            ['name' => 'Pension insurance exemption request'],
            ['name' => 'Declaration of other employments', 'is_owner_uploadable' => true],
            ['name' => 'Working time records (Minimum Wage Act)'],
            ['name' => 'A1 certificate'],
            ['name' => 'Status determination'],
            ['name' => 'Notice from a social insurance agency'],
            ['name' => 'Proof of parenthood', 'is_owner_uploadable' => true],
            ['name' => 'Residence and work permit', 'is_owner_uploadable' => true],
            ['name' => 'Proof of health insurance', 'is_owner_uploadable' => true],
        ];
    }
}

<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Media;
use FluxErp\Rulesets\DocumentType\UpdateDocumentTypeRuleset;
use FluxErp\Support\Media\DocumentRetention;
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

        if ($documentType->wasChanged('retention_years')) {
            resolve_static(Media::class, 'query')
                ->documents()
                ->where('document_type_id', $documentType->getKey())
                ->where('review_state', DocumentReviewStateEnum::Approved)
                ->with('documentType')
                ->get()
                ->each(fn (Media $media) => $media
                    ->forceFill(['retain_until' => max($media->retain_until, app(DocumentRetention::class)($media))])
                    ->save()
                );
        }

        return $documentType->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $documentType = resolve_static(DocumentType::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['is_payroll_record', 'retention_years']);

        $isPayrollRecord = $this->getData('is_payroll_record', $documentType->is_payroll_record);
        $retentionYears = $this->getData('retention_years', $documentType->retention_years);

        if ($isPayrollRecord && ! $retentionYears) {
            throw ValidationException::withMessages([
                'retention_years' => [__('Payroll records (§ 8 BVV) always need a retention period.')],
            ]);
        }
    }
}

<?php

namespace FluxErp\Rulesets\Document;

use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Models\Media;
use FluxErp\Models\Scopes\ExcludeDocumentsScope;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class ReviewDocumentRuleset extends FluxRuleset
{
    protected static ?string $model = Media::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Media::class])
                    ->withoutGlobalScope(ExcludeDocumentsScope::class)
                    ->where('review_state', DocumentReviewStateEnum::Pending),
            ],
            'review_state' => [
                'required',
                Rule::enum(DocumentReviewStateEnum::class)
                    ->only([DocumentReviewStateEnum::Approved, DocumentReviewStateEnum::Rejected]),
            ],
            'review_note' => 'nullable|string',
        ];
    }
}

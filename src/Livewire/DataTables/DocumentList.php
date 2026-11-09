<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Document\DeleteDocument;
use FluxErp\Actions\Document\DownloadDocument;
use FluxErp\Actions\Document\DownloadDocuments;
use FluxErp\Actions\Document\ReviewDocument;
use FluxErp\Actions\Document\UpdateDocument;
use FluxErp\Actions\Document\UploadDocument;
use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Livewire\Forms\DocumentForm;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Media;
use FluxErp\Models\Scopes\ExcludeDocumentsScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\WithFileUploads;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class DocumentList extends BaseDataTable
{
    use WithFileUploads;

    public DocumentForm $documentForm;

    public array $enabledCols = [
        'name',
        'document_type.name',
        'document_date',
        'review_state',
        'retain_until',
        'created_at',
    ];

    public ?string $includeBefore = 'flux::livewire.datatables.document-list';

    #[Locked]
    public ?int $modelId = null;

    #[Locked]
    public ?string $modelType = null;

    public ?string $retainUntil = null;

    public ?string $reviewNote = null;

    #[Locked]
    public ?int $selectedDocumentId = null;

    protected string $model = Media::class;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Upload'))
                ->icon('arrow-up-tray')
                ->color('indigo')
                ->when(fn () => resolve_static(UploadDocument::class, 'canPerformAction', [false]))
                ->wireClick('addDocument()'),
            DataTableButton::make()
                ->text(__('Download all'))
                ->icon('arrow-down-tray')
                ->color('secondary')
                ->when(fn () => resolve_static(DownloadDocuments::class, 'canPerformAction', [false]))
                ->wireClick('downloadDocuments()'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Download'))
                ->icon('arrow-down-tray')
                ->wireClick('downloadDocument(record.id)'),
            DataTableButton::make()
                ->text(__('Review'))
                ->icon('check-badge')
                ->color('indigo')
                ->when(fn () => resolve_static(ReviewDocument::class, 'canPerformAction', [false]))
                ->attributes([
                    'x-cloak' => true,
                    'x-show' => 'record.is_pending',
                    'wire:click' => 'review(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Extend retention'))
                ->icon('calendar')
                ->when(fn () => resolve_static(UpdateDocument::class, 'canPerformAction', [false]))
                ->attributes([
                    'x-cloak' => true,
                    'x-show' => 'record.is_approved',
                    'wire:click' => 'extendRetention(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->when(fn () => resolve_static(DeleteDocument::class, 'canPerformAction', [false]))
                ->attributes([
                    'x-cloak' => true,
                    'x-show' => '! record.is_locked',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Document')]),
                    'wire:click' => 'deleteDocument(record.id)',
                ]),
        ];
    }

    #[Renderless]
    public function addDocument(): void
    {
        $this->documentForm->reset();
        $this->documentForm->document_date = today()->toDateString();

        $this->js(<<<'JS'
            $tsui.open.modal('upload-document-modal');
        JS);
    }

    public function deleteDocument(int $id): bool
    {
        try {
            DeleteDocument::make(['id' => $this->ownDocument($id)->getKey()])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function downloadDocument(int $id): ?StreamedResponse
    {
        try {
            return DownloadDocument::make(['id' => $this->ownDocument($id)->getKey()])
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return null;
        }
    }

    #[Renderless]
    public function downloadDocuments(): ?StreamedResponse
    {
        try {
            return DownloadDocuments::make([
                'model_type' => $this->modelType,
                'model_ids' => [$this->modelId],
            ])
                ->checkPermission()
                ->validate()
                ->execute()
                ->toResponse(request());
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return null;
        }
    }

    #[Renderless]
    public function extendRetention(int $id): void
    {
        $document = $this->ownDocument($id);

        $this->selectedDocumentId = $document->getKey();
        $this->retainUntil = $document->retain_until?->toDateString();

        $this->js(<<<'JS'
            $tsui.open.modal('extend-retention-modal');
        JS);
    }

    #[Renderless]
    public function review(int $id): void
    {
        $this->selectedDocumentId = $this->ownDocument($id)->getKey();
        $this->reviewNote = null;

        $this->js(<<<'JS'
            $tsui.open.modal('review-document-modal');
        JS);
    }

    public function saveRetention(): bool
    {
        try {
            UpdateDocument::make([
                'id' => $this->selectedDocumentId,
                'retain_until' => $this->retainUntil,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function saveReview(string $reviewState): bool
    {
        try {
            ReviewDocument::make([
                'id' => $this->selectedDocumentId,
                'review_state' => $reviewState,
                'review_note' => $this->reviewNote,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function saveUpload(): bool
    {
        $this->documentForm->model_type = $this->modelType;
        $this->documentForm->model_id = $this->modelId;

        try {
            $this->documentForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->documentForm);

            return false;
        }

        $this->documentForm->reset();
        $this->loadData();

        return true;
    }

    protected function augmentItemArray(array &$itemArray, Model $item): void
    {
        /** @var Media $item */
        $itemArray['is_pending'] = $item->review_state === DocumentReviewStateEnum::Pending;
        $itemArray['is_approved'] = $item->review_state === DocumentReviewStateEnum::Approved;
        $itemArray['is_locked'] = $item->isLocked();
    }

    protected function documentTypeQuery(): Builder
    {
        return resolve_static(DocumentType::class, 'query')
            ->where('is_active', true);
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope(ExcludeDocumentsScope::class)
            ->where('model_type', $this->modelType)
            ->where('model_id', $this->modelId)
            ->whereNotNull('document_type_id')
            ->addSelect(['review_state', 'retain_until']);
    }

    protected function getDocumentTypes(): array
    {
        return $this->documentTypeQuery()
            ->orderBy('name')
            ->get(['id', 'name', 'model_types'])
            ->filter(fn (DocumentType $documentType) => $documentType->isAllowedFor($this->modelType))
            ->map(fn (DocumentType $documentType) => [
                'label' => $documentType->name,
                'value' => $documentType->getKey(),
            ])
            ->values()
            ->toArray();
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'documentTypes' => $this->getDocumentTypes(),
            ]
        );
    }

    protected function ownDocument(int $id): Media
    {
        return resolve_static(Media::class, 'query')
            ->withoutGlobalScope(ExcludeDocumentsScope::class)
            ->whereKey($id)
            ->where('model_type', $this->modelType)
            ->where('model_id', $this->modelId)
            ->whereNotNull('document_type_id')
            ->firstOrFail();
    }
}

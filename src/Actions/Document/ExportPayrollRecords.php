<?php

namespace FluxErp\Actions\Document;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Models\Employee;
use FluxErp\Models\Media;
use FluxErp\Models\Scopes\ExcludeDocumentsScope;
use FluxErp\Rulesets\Document\ExportPayrollRecordsRuleset;
use FluxErp\Support\MediaLibrary\FluxMediaStream;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExportPayrollRecords extends FluxAction
{
    public static function models(): array
    {
        return [Media::class];
    }

    protected static function getReturnResult(): bool
    {
        return parent::getReturnResult() ?? true;
    }

    protected function getRulesets(): string|array
    {
        return ExportPayrollRecordsRuleset::class;
    }

    public function performAction(): FluxMediaStream
    {
        $documents = resolve_static(Media::class, 'query')
            ->withoutGlobalScope(ExcludeDocumentsScope::class)
            ->where('model_type', morph_alias(Employee::class))
            ->whereHas('documentType', fn (Builder $query) => $query->where('is_payroll_record', true))
            ->where('document_date', '>=', $this->getData('from'))
            ->where('document_date', '<=', $this->getData('to'))
            ->where('review_state', DocumentReviewStateEnum::Approved)
            ->with(['documentType:id,name', 'nextVersion:id,previous_version_id'])
            ->orderBy('document_date')
            ->get();

        $employees = resolve_static(Employee::class, 'query')
            ->withTrashed()
            ->whereIntegerInRaw('id', $documents->pluck('model_id')->unique()->all())
            ->get()
            ->keyBy('id');

        $documents->each(function (Media $document) use ($employees): void {
            $document->setRelation('model', $employees->get($document->model_id));
            $document->setCustomProperty(
                'zip_filename_prefix',
                collect([
                    $document->document_date->year,
                    $document->documentType->name,
                    $this->personnelNumber($document->model),
                ])
                    ->map(fn (mixed $segment): string => str_replace(['/', '\\'], '-', (string) $segment))
                    ->implode('/') . '/'
            );
        });

        return FluxMediaStream::create('payroll-records.zip')
            ->addMedia($documents)
            ->addFile(
                'index.csv',
                fn (Collection $zipStreamContents): string => $this->index($zipStreamContents)
            );
    }

    protected function index(Collection $zipStreamContents): string
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\u{FEFF}");
        fputcsv(
            $handle,
            [
                __('Personnel number'),
                __('Employee'),
                __('Document Type'),
                __('Document date'),
                __('File'),
                'SHA-256',
                __('File intact'),
                __('Replaced by'),
            ],
            ';'
        );

        $zipStreamContents->each(function (array $mediaInZip) use ($handle): void {
            $media = data_get($mediaInZip, 'media');

            fputcsv(
                $handle,
                [
                    $this->personnelNumber($media->model),
                    $media->model->getLabel(),
                    $media->documentType->name,
                    $media->document_date->toDateString(),
                    data_get($mediaInZip, 'fileNameInZip'),
                    $media->checksum,
                    $media->hasIntactFile() ? __('yes') : __('no'),
                    $media->nextVersion?->getKey(),
                ],
                ';'
            );
        });

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    protected function personnelNumber(Employee $employee): string
    {
        return $employee->employee_number ?: __('ID :id', ['id' => $employee->getKey()]);
    }
}

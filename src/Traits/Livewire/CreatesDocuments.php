<?php

namespace FluxErp\Traits\Livewire;

use BadMethodCallException;
use FluxErp\Actions\Printing;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Jobs\CreateDocumentsJob;
use FluxErp\Livewire\Forms\PrintJobForm;
use FluxErp\Models\Language;
use FluxErp\Models\Printer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait CreatesDocuments
{
    use Actions, EnsureUsedInLivewire;

    #[Locked]
    public array $forcedPrintLayouts = [
        'print' => [],
        'email' => [],
        'download' => [],
        'force' => [],
        'preview' => [],
    ];

    #[Locked]
    public array $previewData = [];

    public PrintJobForm $printJobForm;

    public array $printLayouts = [];

    public array $selectedPrintLayouts = [
        'print' => [],
        'email' => [],
        'download' => [],
        'force' => [],
        'preview' => [],
    ];

    abstract public function createDocuments(): void;

    abstract protected function getPrintLayouts(): array;

    abstract protected function getTo(OffersPrinting $item, array $documents): array;

    #[Renderless]
    public function downloadPreview(): ?StreamedResponse
    {
        if (! $this->supportsDocumentPreview()) {
            throw new BadMethodCallException('Document preview is not supported');
        }

        try {
            $pdf = Printing::make($this->previewData)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return null;
        }

        return response()->streamDownload(
            fn () => print ($pdf->pdf->output()),
            Str::finish($pdf->getFileName(), '.pdf')
        );
    }

    public function mountCreatesDocuments(): void
    {
        if (
            $defaultPrinter = auth()->user()
                ?->printers()
                ->where('printer_user.is_default', true)
                ->first(['id', 'default_size'])
        ) {
            $this->printJobForm->printer_id = $defaultPrinter->id;
            $this->printJobForm->size = $defaultPrinter->default_size;
        }
    }

    #[Renderless]
    public function openCreateDocumentsModal(): void
    {
        $this->printLayouts = array_map(
            fn (string $layout) => ['layout' => $layout, 'label' => __($layout)],
            array_keys($this->getPrintLayouts())
        );

        foreach ($this->getPrintLayouts() as $alias => $className) {
            if (resolve_static($className, 'shouldForceRecreate')) {
                $this->forcedPrintLayouts['force'][] = $alias;
                $this->selectedPrintLayouts['force'][] = $alias;
            }

            if (resolve_static($className, 'shouldForceDownload')) {
                $this->forcedPrintLayouts['download'][] = $alias;
                $this->selectedPrintLayouts['download'][] = $alias;
            }

            if (resolve_static($className, 'shouldForcePrint')) {
                $this->forcedPrintLayouts['print'][] = $alias;
                $this->selectedPrintLayouts['print'][] = $alias;
            }

            if (resolve_static($className, 'shouldForceEmail')) {
                $this->forcedPrintLayouts['email'][] = $alias;
                $this->selectedPrintLayouts['email'][] = $alias;
            }
        }

        $id = strtolower($this->getId());
        $this->js(<<<JS
            \$modalOpen('create-documents-$id');
        JS);
    }

    #[Renderless]
    public function openPreview(string $printView, string $modelType, int|string $modelId): void
    {
        if (! in_array($printView, array_keys($this->getPrintLayouts()))) {
            throw new InvalidArgumentException('Invalid print view');
        }

        if (! $this->supportsDocumentPreview()) {
            throw new BadMethodCallException('Document preview is not supported');
        }

        $this->previewData = [
            'model_type' => $modelType,
            'model_id' => $modelId,
            'view' => $printView,
            'preview' => true,
        ];
        $route = route('print.render', $this->previewData);

        $id = strtolower($this->getId());
        $this->js(<<<JS
            document.getElementById('preview-$id').querySelector('iframe').src = '$route';
            \$modalOpen('preview-$id');
        JS);
    }

    public function renderCreateDocumentsModal(): View
    {
        return view(
            'flux::livewire.create-documents-modal',
            [
                'supportsDocumentPreview' => $this->supportsDocumentPreview(),
                'printers' => auth()->user()
                    ?->printers()
                    ->where('is_active', true)
                    ->get([
                        'id',
                        'name',
                        'alias',
                        'location',
                        'media_sizes',
                    ])
                    ->map(fn (Printer $printer): array => [
                        'id' => $printer->id,
                        'name' => $printer->alias ?? $printer->name,
                        'location' => $printer->location,
                        'media_sizes' => $printer->media_sizes,
                    ])
                    ->toArray() ?? [],
                'mediaSizes' => (
                    $this->printJobForm->printer_id
                        ? auth()->user()
                            ?->printers()
                            ->whereKey($this->printJobForm->printer_id)
                            ->value('media_sizes')
                        : null
                ) ?? [''],
            ]
        );
    }

    protected function createDocumentFromItems(
        Collection|OffersPrinting $items,
        ?string $model = null
    ): void {
        $items = $items instanceof Collection ? $items : collect([$items]);

        if ($items->isEmpty()) {
            return;
        }

        $mailMessages = [];
        $defaultTemplateIds = [];
        $jobItems = [];

        foreach ($items as $item) {
            $item = $this->resolveItem($item, $model);
            if (! $item) {
                continue;
            }

            $this->collectMailMessages($item, $mailMessages, $defaultTemplateIds);

            $queueLayouts = collect(['force', 'print', 'download', 'preview'])
                ->flatMap(fn (string $key) => data_get($this->selectedPrintLayouts, $key) ?? [])
                ->intersect(array_keys($item->resolvePrintViews()))
                ->unique()
                ->values()
                ->all();

            if ($queueLayouts) {
                $jobItems[] = [
                    'model_type' => $item->getMorphClass(),
                    'model_id' => $item->getKey(),
                    'layouts' => $queueLayouts,
                ];
            }
        }

        $this->dispatchMailMessages($mailMessages, $defaultTemplateIds);

        if ($jobItems) {
            $job = app(CreateDocumentsJob::class, [
                'items' => $jobItems,
                'selectedPrintLayouts' => $this->selectedPrintLayouts,
                'userMorph' => auth()->user()->getMorphClass() . ':' . auth()->id(),
                'printerId' => $this->printJobForm->printer_id,
                'printerSize' => $this->printJobForm->size,
                'printerQuantity' => $this->printJobForm->quantity,
            ]);

            if ($items->count() > 1) {
                dispatch($job);

                $this->toast()
                    ->success(
                        __('Documents are being created'),
                        __('You will be notified when they are ready.')
                    )
                    ->send();
            } else {
                try {
                    dispatch_sync($job->throwException());
                } catch (ValidationException|UnauthorizedException $e) {
                    exception_to_notifications($e, $this);
                }
            }
        }
    }

    protected function collectMailMessages(
        OffersPrinting $item,
        array &$mailMessages,
        array &$defaultTemplateIds
    ): void {
        $emailLayouts = data_get($this->selectedPrintLayouts, 'email') ?? [];
        $emailDocuments = collect($emailLayouts)
            ->intersect(array_keys($item->resolvePrintViews()))
            ->unique()
            ->all();

        $mailAttachments = [];
        foreach ($emailDocuments as $createDocument) {
            $media = $item instanceof HasMedia ? $item->getMedia($createDocument)->last() : null;

            if ($media && ! $media->isTemporary) {
                $mailAttachments[] = [
                    'name' => $media->file_name,
                    'id' => $media->getKey(),
                ];
            } else {
                $mailAttachments[] = $this->getCreateAttachmentArray($item, $createDocument);
            }
        }

        if (! $emailLayouts || ! $mailAttachments) {
            return;
        }

        $item->refresh();
        $bladeParameters = method_exists($this, 'getBladeParameters')
            ? $this->getBladeParameters($item)
            : [];

        if ($bladeParameters instanceof SerializableClosure) {
            $bladeParameters = serialize($bladeParameters);
        }

        $mailAttachments[] = $this->getAttachments($item);
        $mailMessage = [
            'to' => $this->getTo($item, $emailDocuments),
            'cc' => $this->getCc($item),
            'bcc' => $this->getBcc($item),
            'subject' => $this->getSubject($item, $emailDocuments),
            'attachments' => array_filter($mailAttachments),
            'html_body' => null,
            'blade_parameters_serialized' => is_string($bladeParameters),
            'blade_parameters' => $bladeParameters,
            'communicatables' => [
                [
                    'communicatable_type' => $this->getCommunicatableType($item),
                    'communicatable_id' => $this->getCommunicatableId($item),
                ],
            ],
        ];

        $mailMessage['model_type'] = $item->getEmailTemplateModelType();
        $mailMessage['language_id'] = $this->getPreferredLanguageId($item);
        $mailMessage['group_key'] = $this->getMailGroupKey($item);
        $mailMessage['group_label'] = $this->getMailGroupLabel($item);

        if (method_exists($this, 'getDefaultTemplateId')) {
            $emailTemplateId = $this->getDefaultTemplateId($item);
            $mailMessage['default_template_id'] = $emailTemplateId;

            if ($emailTemplateId !== null) {
                $defaultTemplateIds[] = $emailTemplateId;
            }
        }

        $mailMessages[] = $mailMessage;
    }

    protected function dispatchMailMessages(array $mailMessages, array $defaultTemplateIds): void
    {
        if (! $mailMessages) {
            return;
        }

        $uniqueTemplateIds = collect($defaultTemplateIds)->unique();
        if ($uniqueTemplateIds->count() === 1 && count($mailMessages) > 1) {
            $commonTemplateId = $uniqueTemplateIds->first();
            foreach ($mailMessages as &$mailMessage) {
                $mailMessage['default_template_id'] = $commonTemplateId;
            }
        }

        $sessionKey = 'mail_' . Str::uuid()->toString();
        session()->put($sessionKey, $mailMessages);
        $this->dispatch('createFromSession', key: $sessionKey)->to('edit-mail');
    }

    protected function resolveItem(mixed $item, ?string $model = null): ?OffersPrinting
    {
        $item = match (true) {
            is_a($item, OffersPrinting::class) => $item,
            is_int($item) && $model => resolve_static($model, 'query')->whereKey($item)->first(),
            default => null,
        };

        return $item instanceof OffersPrinting ? $item : null;
    }

    protected function getAttachments(OffersPrinting $item): array
    {
        return [];
    }

    protected function getBcc(OffersPrinting $item): array
    {
        return [];
    }

    protected function getBladeParameters(): array|SerializableClosure|null
    {
        return null;
    }

    protected function getCc(OffersPrinting $item): array
    {
        return [];
    }

    protected function getCommunicatableId(OffersPrinting $item): int
    {
        return $item->getKey();
    }

    protected function getCommunicatableType(OffersPrinting $item): string
    {
        return $item->getMorphClass();
    }

    protected function getCreateAttachmentArray(OffersPrinting $item, string $view): array
    {
        return [
            'model_type' => $item->getMorphClass(),
            'model_id' => $item->getKey(),
            'view' => $view,
            'name' => __($view),
        ];
    }

    protected function getMailGroupKey(OffersPrinting $item): string
    {
        return (string) ($this->getPreferredLanguageId($item) ?? 'default');
    }

    protected function getMailGroupLabel(OffersPrinting $item): ?string
    {
        $languageId = $this->getPreferredLanguageId($item);

        return $languageId
            ? resolve_static(Language::class, 'query')
                ->whereKey($languageId)
                ->value('name')
            : __('Default');
    }

    protected function getPreferredLanguageId(OffersPrinting $item): ?int
    {
        return resolve_static(Language::class, 'default')?->getKey();
    }

    protected function getSubject(OffersPrinting $item, array $documents): ?string
    {
        return null;
    }

    protected function supportsDocumentPreview(): bool
    {
        return false;
    }
}

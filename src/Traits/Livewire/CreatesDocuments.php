<?php

namespace FluxErp\Traits\Livewire;

use BadMethodCallException;
use FluxErp\Actions\Printing;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Livewire\Forms\PrintJobForm;
use FluxErp\Models\Media;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\Support\MediaStream;
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
    ];

    abstract public function createDocuments(): null|MediaStream|Media;

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
                        'location',
                        'media_sizes',
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
        bool $async = false,
        ?string $model = null
    ): null|MediaStream|Media {
        $items = $items instanceof Collection ? $items : collect([$items]);

        if ($items->isEmpty()) {
            return null;
        }

        $downloadIds = [];
        $downloadItems = [];
        $printIds = [];
        $mailMessages = [];
        $defaultTemplateIds = [];

        foreach ($items as $item) {
            match ($item) {
                is_a($item, OffersPrinting::class, true) => $item->fresh(),
                is_int($item) && $model => $item = resolve_static($model, 'query')->whereKey($item)->first(),
                default => null,
            };

            if (! $item) {
                continue;
            }

            $mailAttachments = [];
            $createDocuments = array_unique(
                array_intersect(
                    Arr::flatten($this->selectedPrintLayouts),
                    array_keys($item->resolvePrintViews())
                )
            );

            // create the documents
            foreach ($createDocuments as $createDocument) {
                $media = is_a($item, HasMedia::class) ? $item->getMedia($createDocument)->last() : null;
                $isDownload = in_array($createDocument, data_get($this->selectedPrintLayouts, 'download', []));
                $isPrint = in_array($createDocument, data_get($this->selectedPrintLayouts, 'print', []));
                $isForce = in_array($createDocument, data_get($this->selectedPrintLayouts, 'force', []));
                $isEmail = in_array($createDocument, data_get($this->selectedPrintLayouts, 'email', []));

                if ((! $media && ($isDownload || $isPrint)) || $isForce || (! $async && ! $media)) {
                    try {
                        /** @var PrintableView $file */
                        $file = Printing::make([
                            'model_type' => $item->getMorphClass(),
                            'model_id' => $item->getKey(),
                            'view' => $createDocument,
                        ])
                            ->checkPermission()
                            ->validate()
                            ->execute();

                        if ($file->shouldStore()) {
                            $media = $file->attachToModel($item);
                        } else {
                            $fileName = tempnam(sys_get_temp_dir(), 'flux-print-') . '.pdf';
                            $file->savePDF($fileName);

                            $media = app(Media::class, ['attributes' => [
                                'name' => $file->getFileName(),
                                'file_name' => $file->getFileName() . '.pdf',
                                'mime_type' => 'application/pdf',
                                'disk' => 'local',
                                'conversions_disk' => 'local',
                            ]])
                                ->setPath($fileName)
                                ->setIsTemporary()
                                ->setKeyType('string');
                            $media->id = Str::uuid()->toString();
                        }
                    } catch (ValidationException|UnauthorizedException $e) {
                        exception_to_notifications($e, $this);

                        continue;
                    }
                }

                if ($isDownload) {
                    if ($media->getKey() && ! $media->isTemporary) {
                        $downloadIds[] = $media->getKey();
                    } else {
                        $downloadItems[] = $media;
                    }
                }

                if ($isPrint) {
                    $printIds[] = $media->getKey();
                }

                if ($isEmail) {
                    if ($media && ! $media->isTemporary) {
                        $mailAttachments[] = [
                            'name' => $media->file_name,
                            'id' => $media->getKey(),
                        ];
                    } else {
                        $mailAttachments[] = $this->getCreateAttachmentArray($item, $createDocument);
                    }
                }
            }

            if (data_get($this->selectedPrintLayouts, 'email', false) && $mailAttachments) {
                $item->refresh();
                $bladeParameters = method_exists($this, 'getBladeParameters')
                    ? $this->getBladeParameters($item)
                    : [];

                if ($bladeParameters instanceof SerializableClosure) {
                    $bladeParameters = serialize($bladeParameters);
                }

                $mailAttachments[] = $this->getAttachments($item);
                $mailMessage = [
                    'to' => $this->getTo($item, $createDocuments),
                    'cc' => $this->getCc($item),
                    'bcc' => $this->getBcc($item),
                    'subject' => null,
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
                if (method_exists($this, 'getDefaultTemplateId')) {
                    $emailTemplateId = $this->getDefaultTemplateId($item);
                    $mailMessage['default_template_id'] = $emailTemplateId;
                    if ($emailTemplateId !== null) {
                        $defaultTemplateIds[] = $emailTemplateId;
                    }
                }

                $mailMessages[] = $mailMessage;
            }
        }

        if ($mailMessages) {
            // Check if all items have the same default template ID
            $uniqueTemplateIds = collect($defaultTemplateIds)->unique();
            if ($uniqueTemplateIds->count() === 1 && count($mailMessages) > 1) {
                // Set the common template ID for all messages
                $commonTemplateId = $uniqueTemplateIds->first();
                foreach ($mailMessages as &$mailMessage) {
                    $mailMessage['default_template_id'] = $commonTemplateId;
                }
            }

            $sessionKey = 'mail_' . Str::uuid()->toString();
            session()->put($sessionKey, $mailMessages);
            $this->dispatch('createFromSession', key: $sessionKey)->to('edit-mail');
        }

        if ($downloadIds || $downloadItems) {
            /** @var \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection $files */
            $files = resolve_static(Media::class, 'query')
                ->whereIntegerInRaw('id', $downloadIds)
                ->get();

            foreach ($downloadItems as $downloadItem) {
                $files->add($downloadItem);
            }

            if ($files->count() === 1) {
                return $files->first();
            }

            return MediaStream::create(
                __(Str::of(class_basename(static::class))->headline()->snake()->toString())
                . '_' . now()->toDateString() . '.zip'
            )
                ->addMedia($files);
        }

        if ($printIds && $this->printJobForm->printer_id) {
            foreach ($printIds as $printId) {
                $this->printJobForm->reset('id');
                $this->printJobForm->media_id = $printId;

                try {
                    $this->printJobForm->save();
                } catch (ValidationException|UnauthorizedException $e) {
                    exception_to_notifications($e, $this);
                }
            }

            $this->printJobForm->reset();
        }

        return null;
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

    protected function supportsDocumentPreview(): bool
    {
        return false;
    }
}

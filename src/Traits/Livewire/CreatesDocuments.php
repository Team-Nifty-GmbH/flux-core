<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Actions\Printing;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Models\Media;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Attributes\Renderless;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

trait CreatesDocuments
{
    use Actions, EnsureUsedInLivewire;

    public array $printLayouts = [];

    public array $selectedPrintLayouts = [
        'print' => [],
        'email' => [],
        'download' => [],
        'force' => [],
    ];

    public function renderCreateDocumentsModal(): View
    {
        return view('flux::livewire.create-documents-modal');
    }

    #[Renderless]
    public function openCreateDocumentsModal(): void
    {
        $this->printLayouts = array_map(
            fn (string $layout) => ['layout' => $layout, 'label' => __($layout)],
            $this->getPrintLayouts()
        );

        $this->js(<<<'JS'
            $openModal('create-documents');
        JS);
    }

    #[Renderless]
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
        $printIds = [];
        $mailMessages = [];
        foreach ($items as $item) {
            match ($item) {
                is_a($item, OffersPrinting::class, true) => $item->fresh(),
                is_int($item) && $model => $item = app($model)->query()->whereKey($item)->first(),
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

                if ((! $media && ($isDownload || $isPrint)) || $isForce || ! $async) {
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

                        $media = $file->attachToModel($item);
                    } catch (ValidationException|UnauthorizedException $e) {
                        exception_to_notifications($e, $this);

                        continue;
                    }
                }

                if ($isDownload) {
                    $downloadIds[] = $media->getKey();
                }

                if ($isPrint) {
                    // TODO: add to print queue for spooler
                    $printIds[] = $media->getKey();
                }

                if ($isEmail) {
                    if ($media) {
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
                $mailMessages[] = [
                    'to' => array_unique($this->getTo($item, $createDocuments)),
                    'cc' => $this->getCc($item),
                    'bcc' => $this->getBcc($item),
                    'subject' => $this->getSubject($item),
                    'attachments' => array_filter($mailAttachments),
                    'html_body' => $this->getHtmlBody($item),
                    'blade_parameters_serialized' => is_string($bladeParameters),
                    'blade_parameters' => $bladeParameters,
                    'communicatable_type' => $this->getCommunicatableType($item),
                    'communicatable_id' => $this->getCommunicatableId($item),
                ];
            }
        }
        if ($mailMessages) {
            $sessionKey = 'mail_' . Str::uuid()->toString();
            session()->put($sessionKey, $mailMessages);
            $this->dispatch('createFromSession', key: $sessionKey)->to('edit-mail');
        }

        if ($downloadIds) {
            $files = app(Media::class)->query()
                ->whereIntegerInRaw('id', $downloadIds)
                ->get();

            if ($files->count() === 1) {
                return $files->first();
            }

            return MediaStream::create(
                __(Str::of(class_basename(static::class))->headline()->snake()->toString())
                . '_' . now()->toDateString() . '.zip'
            )
                ->addMedia($files);
        }

        return null;
    }

    protected function getBladeParameters(): array|SerializableClosure|null
    {
        return null;
    }

    protected function getCommunicatableType(OffersPrinting $item): string
    {
        return $item->getMorphClass();
    }

    protected function getCommunicatableId(OffersPrinting $item): int
    {
        return $item->getKey();
    }

    protected function getAttachments(OffersPrinting $item): array
    {
        return [];
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

    protected function getCc(OffersPrinting $item): array
    {
        return [];
    }

    protected function getBcc(OffersPrinting $item): array
    {
        return [];
    }

    abstract protected function getTo(OffersPrinting $item, array $documents): array;

    abstract protected function getSubject(OffersPrinting $item): string;

    abstract protected function getHtmlBody(OffersPrinting $item): string;

    abstract protected function getPrintLayouts(): array;

    abstract public function createDocuments(): null|MediaStream|Media;
}

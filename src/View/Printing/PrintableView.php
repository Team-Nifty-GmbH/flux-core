<?php

namespace FluxErp\View\Printing;

use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Barryvdh\DomPDF\PDF;
use Dompdf\Canvas;
use Dompdf\FontMetrics;
use Dompdf\Options;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Contracts\SignablePrintView;
use FluxErp\Models\Client;
use FluxErp\Models\PrintLayout;
use FluxErp\Printing\Printable;
use FluxErp\Traits\Makeable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Imagick;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class PrintableView extends Component
{
    use Makeable;

    private static ?string $layout = 'flux::layouts.printing';

    public PDF $pdf;

    public bool $preview = false;

    private ?Imagick $imagick = null;

    abstract public function getFileName(): string;

    abstract public function getModel(): ?Model;

    abstract public function getSubject(): string;

    public static function getLayout(): ?string
    {
        return static::$layout;
    }

    public static function setLayout(?string $layout): void
    {
        static::$layout = $layout;
    }

    public static function shouldForceDownload(): bool
    {
        return false;
    }

    public static function shouldForceEmail(): bool
    {
        return false;
    }

    public static function shouldForcePrint(): bool
    {
        return false;
    }

    public static function shouldForceRecreate(): bool
    {
        return false;
    }

    public function attachToModel(?Model $model = null): ?Media
    {
        $model = is_null($model) ? $this->getModel() : $model;

        if (! $model instanceof Model
            || ! $model instanceof HasMedia
        ) {
            return null;
        }

        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, $this->pdf->output());
        rewind($resource);

        $data = [
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
            'collection_name' => $this->getCollectionName(),
            'file_name' => now()->format('Y-m-d_H-i-s') . '_' . Str::finish($this->getFileName(), '.pdf'),
            'name' => now()->format('Y-m-d_H-i-s') . '_' . $this->getFileName(),
            'media' => $resource,
            'media_type' => 'stream',
        ];

        return UploadMedia::make($data)
            ->force()
            ->validate()
            ->execute();
    }

    public function getPrintLayout(): ?array
    {
        $model = $this->getModel();
        if ($model?->client_id) {
            return resolve_static(PrintLayout::class, 'query')
                ->where('client_id', $model->client_id)
                ->where('model_type', morph_alias($model::class))
                ->where('name', static::getLayout() . '.' . morph_alias($model::class) . '.' . strtolower($model->orderType->name))
                ->first()?->toArray();
        }

        return null;
    }

    public function preview(bool $preview = true): static
    {
        $this->preview = $preview;

        return $this;
    }

    public function print(): static
    {
        if (method_exists($this, 'beforePrinting')) {
            $this->beforePrinting();
        }

        $this->hydrateSharedData();
        File::ensureDirectoryExists(storage_path('fonts'));

        $this->pdf = PdfFacade::loadHTML($this->renderWithLayout(true))
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultMediaType', 'print')
            ->setOption('fontHeightRatio', 0.8)
            ->setPaper($this->getPaperSize(), $this->getPaperOrientation());

        if (! config('dompdf.options.allowed_remote_hosts')) {
            $this->pdf->setOption(
                'allowedRemoteHosts',
                array_filter([
                    'localhost',
                    '127.0.0.1',
                    'fonts.googleapis.com',
                    'fonts.gstatic.com',
                    Str::after(config('app.url'), '://'),
                    Str::after(config('app.asset_url'), '://'),
                    Str::after(config('app.frontend_url'), '://'),
                    Str::after(config('flux.flux_url'), '://'),
                ])
            );
        }

        $this->pdf->render();

        Printable::injectPageCount($this->pdf);

        if ($this->preview) {
            $canvas = $this->pdf->getCanvas();

            $fontMetrics = new FontMetrics($canvas, new Options(['isPhpEnabled' => true]));
            $text = __('Preview');
            $font = $fontMetrics->getFont('sans-serif');
            $textHeight = $fontMetrics->getFontHeight($font, 110);
            $textWidth = $fontMetrics->getTextWidth($text, $font, 110);

            $canvasWidth = $canvas->get_width();
            $canvasHeight = $canvas->get_height();
            $textX = ($canvasWidth - $textWidth) / 2;
            $textY = ($canvasHeight - $textHeight) / 2;

            $canvas->set_opacity(0.2, 'Multiply');
            $canvas->page_script(
                function (
                    int $pageNumber,
                    int $pageCount,
                    Canvas $canvas
                ) use ($textX, $textY, $canvasWidth, $canvasHeight, $text, $font): void {
                    $canvas->rotate(45, $canvasWidth / 2, $canvasHeight / 2);
                    $canvas->text($textX, $textY, $text, $font, 110);
                    $canvas->set_opacity(0.2, 'Multiply');
                }
            );
        }

        if (method_exists($this, 'beforePrinting')) {
            $this->beforePrinting();
        }

        if (! $this->preview) {
            $model = $this->getModel();
            activity()
                ->performedOn($model)
                ->event('pdf_created')
                ->log(
                    __(
                        ':view PDF created',
                        ['view' => data_get(array_flip($model->resolvePrintViews()), static::class)]
                    )
                );
        }

        return $this;
    }

    public function renderAndHydrate(): \Illuminate\View\View|string
    {
        $this->hydrateSharedData();

        return $this->renderWithLayout();
    }

    public function savePDF(?string $fileName = null, ?string $disk = null): PDF
    {
        return $this->pdf->save(Str::finish($fileName ?? $this->getFileName(), '.pdf'), $disk);
    }

    public function shouldStore(): bool
    {
        return true;
    }

    public function streamPDF(?string $fileName = null): Response
    {
        return $this->pdf->stream(Str::finish($fileName ?? $this->getFileName(), '.pdf'));
    }

    protected function getCollectionName(): string
    {
        return Str::kebab(class_basename($this));
    }

    protected function getPageCss(): array
    {
        // add margin for first page - to avoid header on first page
        $model = $this->getModel();
        if ($model &&  data_get($model, 'client_id')) {
            $layout = $this->getPrintLayout();
            if ($layout && data_get($layout, 'margin') && data_get($layout, 'header') && data_get($layout, 'footer')) {
                $margin = data_get($layout, 'margin');

                // due to rounding issues -> px to cm -> add 0.1cm to header height
                return [
                    'header_height' => (data_get($layout, 'header.height', 0) + 0.1) . 'cm',
                    'footer_height' => data_get($layout, 'footer.height') . 'cm',
                    'first_page_header_margin_top' => data_get($margin, 'marginTop', '0') . 'cm',
                    'margin_preview_view' => [
                        data_get($margin, 'marginTop', '0') . 'cm',
                        data_get($margin, 'marginRight', '0') . 'cm',
                        data_get($margin, 'marginBottom', '0') . 'cm',
                        data_get($margin, 'marginLeft', '0') . 'cm',
                    ],
                    'margin_first_page' => [
                        '0cm',
                        data_get($margin, 'marginRight', '0') . 'cm',
                        (data_get($margin, 'marginBottom', 0) + data_get($layout, 'footer.height', 0)) . 'cm',
                        data_get($margin, 'marginLeft', '0') . 'cm',
                    ],
                    'margin' => [
                        (data_get($margin, 'marginTop', 0) + data_get($layout, 'header.height', 0)) . 'cm',
                        data_get($margin, 'marginRight', '0') . 'cm',
                        (data_get($margin, 'marginBottom', 0) + data_get($layout, 'footer.height', 0)) . 'cm',
                        data_get($margin, 'marginLeft', '0') . 'cm',
                    ]];
            }
        }

        return [
            'header_height' => '18mm',
            'footer_height' => '17mm',
            'first_page_header_margin_top' => '32mm',
            'margin_preview_view' => ['32mm', '20mm', '28mm', '18mm'],
            'margin_first_page' => ['0mm', '20mm', '28mm', '18mm'],
            'margin' => ['50mm', '20mm', '45mm', '18mm'],
        ];
    }

    protected function getPaperOrientation(): string
    {
        return 'portrait';
    }

    protected function getPaperSize(): array|string
    {
        return 'A4';
    }

    protected function hydrateSharedData(): void
    {
        $model = $this->getModel();

        $client = $model?->client ?? Client::query()->first();

        if (($logo = $client->getFirstMedia('logo')) && file_exists($logo->getPath())) {
            $client->logo = File::mimeType($logo->getPath()) === 'image/svg+xml'
                ? $logo->getUrl('png')
                : $logo->getUrl();
        }

        if (($logoSmall = $client->getFirstMedia('logo_small')) && file_exists($logoSmall->getPath())) {
            $client->logo_small = File::mimeType($logoSmall->getPath()) === 'image/svg+xml'
                ? $logoSmall->getUrl('png')
                : $logoSmall->getUrl();
        }

        $signaturePath = null;
        if (is_a(static::class, SignablePrintView::class, true)) {
            $viewAlias = data_get(array_flip($model->resolvePrintViews()), static::class);
            $signaturePath = $model->media()
                ->where('collection_name', 'signature')
                ->where('name', 'signature-' . $viewAlias)
                ->first()
                ?->getPath();
        }

        View::share('signaturePath', $signaturePath);
        View::share('client', $client);
        View::share('subject', $this->getSubject());
        View::share('printView', Str::kebab(class_basename($this)));
        View::share('printLayout', static::$layout);

        $this->imagick?->clear();
        $this->imagick?->destroy();
    }

    protected function renderFooter(): bool
    {
        return true;
    }

    protected function renderHeader(): bool
    {
        return true;
    }

    protected function renderWithLayout(bool $generatePdf = false): \Illuminate\View\View
    {
        return is_null(static::$layout)
            ? $this->render()
            : view(
                static::$layout,
                [
                    'slot' => $this->render(),
                    'pageCss' => $this->getPageCss(),
                    'hasHeader' => $this->renderHeader(),
                    'hasFooter' => $this->renderFooter(),
                    'layout' => $this->getPrintLayout(),
                    'generatePdf' => $generatePdf,
                ]
            );
    }
}

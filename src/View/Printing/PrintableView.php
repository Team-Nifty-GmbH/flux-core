<?php

namespace FluxErp\View\Printing;

use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Barryvdh\DomPDF\PDF;
use Dompdf\Canvas;
use Dompdf\FontMetrics;
use Dompdf\Options;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\Client;
use FluxErp\Printing\Printable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class PrintableView extends Component
{
    public PDF $pdf;

    private ?\Imagick $imagick = null;

    abstract public function getModel(): ?Model;

    abstract public function getFileName(): string;

    abstract public function getSubject(): string;

    public bool $preview = false;

    public function preview(bool $preview = true): static
    {
        $this->preview = $preview;

        return $this;
    }

    protected function hydrateSharedData(): void
    {
        $client = $this->getModel()?->client ?? Client::query()->first();

        $logo = $client->getFirstMedia('logo')->getPath();
        $logoSmall = $client->getFirstMedia('logo_small')->getPath();

        $logoCacheKey = 'logo_' . md5(file_get_contents($logo));
        $logoSmallCacheKey = 'logo_' . md5(file_get_contents($logoSmall));
        $mimeTypeLogo = File::mimeType($logo);
        $mimeTypeLogoSmall = File::mimeType($logoSmall);

        if ($mimeTypeLogo === 'image/svg+xml' && ! Cache::driver('file')->has($logoCacheKey)) {
            $logo = $this->convertSvg($logo, $logoCacheKey);
        } else {
            $logo = Cache::driver('file')->get($logoCacheKey) ?? file_get_contents($logo);
        }

        if ($mimeTypeLogoSmall === 'image/svg+xml' && ! Cache::driver('file')->has($logoSmallCacheKey)) {
            $logoSmall = $this->convertSvg($logoSmall, $logoSmallCacheKey);
        } else {
            $logoSmall = Cache::driver('file')->get($logoSmallCacheKey) ?? file_get_contents($logoSmall);
        }

        $client->logo = 'data:image/' . $mimeTypeLogo . ';base64,' . base64_encode($logo);
        $client->logo_small = 'data:image/' . $mimeTypeLogoSmall . ';base64,' . base64_encode($logoSmall);

        View::share('client', $client);
        View::share('subject', $this->getSubject());

        $this->imagick?->clear();
        $this->imagick?->destroy();
    }

    public function print(): static
    {
        if (method_exists($this, 'beforePrinting')) {
            $this->beforePrinting();
        }

        $this->hydrateSharedData();
        File::ensureDirectoryExists(storage_path('fonts'));

        $this->pdf = PdfFacade::loadHTML($this->render())
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('isPhpEnabled', true)
            ->setOption('defaultMediaType', 'print');
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
                ) use ($textX, $textY, $canvasWidth, $canvasHeight, $text, $font) {
                    $canvas->rotate(45, $canvasWidth / 2, $canvasHeight / 2);
                    $canvas->text($textX, $textY, $text, $font, 110);
                    $canvas->set_opacity(0.2, 'Multiply');
                }
            );
        }

        if (method_exists($this, 'beforePrinting')) {
            $this->beforePrinting();
        }

        return $this;
    }

    public function renderAndHydrate(): \Illuminate\View\View
    {
        $this->hydrateSharedData();

        return $this->render();
    }

    public function streamPDF(?string $fileName = null): Response
    {
        return $this->pdf->stream(Str::finish($fileName ?? $this->getFileName(), '.pdf'));
    }

    public function savePDF(?string $fileName = null, ?string $disk = null): PDF
    {
        return $this->pdf->save(Str::finish($fileName ?? $this->getFileName(), '.pdf'), $disk);
    }

    protected function getCollectionName(): string
    {
        return strtolower(class_basename($this));
    }

    public function attachToModel(): ?Media
    {
        if (! $this->getModel() instanceof Model
            || in_array(HasMedia::class, class_uses_recursive($this->getModel()))
        ) {
            return null;
        }

        $resource = fopen('php://memory', 'rb+');
        fwrite($resource, $this->pdf->output());
        rewind($resource);

        $data = [
            'model_type' => get_class($this->getModel()),
            'model_id' => $this->getModel()->getKey(),
            'collection_name' => $this->getCollectionName(),
            'file_name' => now()->toString() . '_' . Str::finish($this->getFileName(), '.pdf'),
            'name' => now()->toString() . '_' . $this->getFileName(),
            'media' => $resource,
            'media_type' => 'stream',
        ];

        return UploadMedia::make($data)->validate()
            ->execute();
    }

    private function convertSvg(string $path, string $cacheKey): string
    {
        if (! $this->imagick) {
            $this->imagick = new \Imagick();
            $this->imagick->setResolution(300, 300);
        }

        $this->imagick->readImage($path);
        $this->imagick->setImageFormat('png');
        $this->imagick->setImageCompressionQuality(100);
        $logo = $this->imagick->getImageBlob();
        Cache::driver('file')->put($cacheKey, $logo);

        return $logo;
    }
}

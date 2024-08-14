<?php

namespace FluxErp\Printing;

use Barryvdh\DomPDF\PDF;
use Dompdf\Adapter\CPDF;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Factory;
use Illuminate\View\View;
use InvalidArgumentException;

class Printable
{
    public array $views = [];

    public bool $preview = false;

    public function __construct(public Model|Collection $dataSet)
    {
        if (! class_implements($dataSet, OffersPrinting::class)) {
            throw new InvalidArgumentException(
                get_class($this->dataSet).' doesnt implement PrintableInterface'
            );
        }

        $this->views = $dataSet->resolvePrintViews();

        return $this;
    }

    public function preview(bool $preview = true): static
    {
        $this->preview = $preview;

        return $this;
    }

    public function __call(string $name, mixed $arguments)
    {
        if (str_starts_with($name, 'print')) {
            $viewName = strtolower(str_replace('print', '', $name));

            return $this->printView($this->getViewClass($viewName), ...$arguments);
        } elseif (str_starts_with($name, 'render')) {
            $viewName = strtolower(str_replace('render', '', $name));

            return $this->renderView($this->getViewClass($viewName));
        }

        throw new InvalidArgumentException('Method '.$name.' doesnt exist');
    }

    public function getViewClass(string $name): string
    {
        $view = $this->views[$name] ?? null;

        if (! $view) {
            throw new InvalidArgumentException('No view found for '.$name);
        }

        return resolve_static($view, 'class');
    }

    public function printView(string $view, ...$arguments): PrintableView
    {
        /** @var PrintableView $view */
        return $view::make($this->dataSet)->preview($this->preview)->print(...$arguments);
    }

    public function renderView(string $view): View|Factory
    {
        /** @var PrintableView $view */
        return $view::make($this->dataSet)->renderAndHydrate();
    }

    public static function injectPageCount(PDF $PDF): void
    {
        $canvas = $PDF->getCanvas();
        assert($canvas instanceof CPDF);
        $search = static::insertNullByteBeforeEachCharacter('DOMPDF_PAGE_COUNT_PLACEHOLDER');
        $replace = static::insertNullByteBeforeEachCharacter((string) $canvas->get_page_count());

        foreach ($canvas->get_cpdf()->objects as &$object) {
            if ($object['t'] === 'contents') {
                $object['c'] = str_replace($search, $replace, $object['c']);
            }
        }
    }

    private static function insertNullByteBeforeEachCharacter(string $string): string
    {
        return "\u{0000}".substr(chunk_split($string, 1, "\u{0000}"), 0, -1);
    }
}

<?php

namespace FluxErp\Contracts;

use Closure;
use FluxErp\Printing\Printable;

interface OffersPrinting
{
    public static function registerPrintView(string $name, Closure|string $viewClass): void;

    public function getAvailableViews(): array;

    public function getPrintViews(): array;

    public function print(): Printable;

    public function resolvePrintViews(): array;
}

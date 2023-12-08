<?php

namespace FluxErp\Contracts;

use FluxErp\Printing\Printable;

interface OffersPrinting
{
    public static function registerPrintView(string $name, \Closure|string $viewClass): void;

    public function getPrintViews(): array;

    public function getAvailableViews(): array;

    public function resolvePrintViews(): array;

    public function print(): Printable;
}

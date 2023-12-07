<?php

namespace FluxErp\Contracts;

use FluxErp\Printing\Printable;

interface OffersPrinting
{
    public function getPrintViews(): array;

    public function print(): Printable;

    public static function registerPrintView(string $name, \Closure|string $viewClass): void;

    public function resolvePrintViews(): array;

    public function getAvailableViews(): array;
}

<?php

namespace FluxErp\View\Printing\Address;

use FluxErp\Models\Address;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class AddressLabel extends PrintableView
{
    public function __construct(public Address $address) {}

    public static function shouldForceRecreate(): bool
    {
        return true;
    }

    public function render(): View
    {
        return view('print::address.address-label', [
            'model' => $this->address,
        ]);
    }

    public function getFileName(): string
    {
        return $this->getSubject();
    }

    public function getModel(): ?Model
    {
        return $this->address;
    }

    public function getSubject(): string
    {
        return __('Address Label') . ' ' . $this->address->name;
    }

    public function shouldStore(): bool
    {
        return false;
    }

    protected function getPageCss(): array
    {
        return [
            'margin' => [
                'top' => 5,
                'right' => 15,
                'bottom' => 5,
                'left' => 15,
            ],
        ];
    }

    protected function getPaperSize(): array
    {
        return [0, 0, 252.28, 79.37];
    }

    protected function renderFooter(): bool
    {
        return false;
    }

    protected function renderHeader(): bool
    {
        return false;
    }
}

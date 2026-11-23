<?php

use FluxErp\Traits\Livewire\CreatesDocuments;
use FluxErp\View\Printing\Contact\BalanceStatement;
use FluxErp\View\Printing\Order\Invoice;
use Livewire\Component;

test('tells the documents dialog which layouts can be printed', function (): void {
    $notPrintable = new class() extends Invoice
    {
        public function __construct() {}

        public static function isPrintable(): bool
        {
            return false;
        }
    };

    $component = new class() extends Component
    {
        use CreatesDocuments;

        public array $layouts = [];

        public function render()
        {
            return view('flux::livewire.support.dashboard');
        }

        public function createDocuments(): void {}

        public function getPrintLayouts(): array
        {
            return $this->layouts;
        }

        public function getTo($item, array $documents): array
        {
            return [];
        }

        public function mapped(): array
        {
            return $this->mapPrintLayouts();
        }
    };

    $component->layouts = [
        'balance-statement' => BalanceStatement::class,
        'not-printable' => $notPrintable::class,
    ];

    expect(array_column($component->mapped(), 'is_printable', 'layout'))->toBe([
        'balance-statement' => true,
        'not-printable' => false,
    ]);
});

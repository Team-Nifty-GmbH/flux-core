<?php

namespace FluxErp\Livewire;

use Illuminate\View\View;
use Illuminate\Support\Fluent;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PrintLayoutEditor extends Component
{
    public array $client;

    public string $subject = 'Print Layout Editor';

    public function mount(): void
    {
        $this->client= ['logo_small' => asset('/pwa-icons/icons-192.png')];
    }

    public function getClientFluentProperty()
    {
        return new Fluent($this->client);
    }

    public function orderPrint():View
    {
        return view('livewire.printing.order.order');
    }

    #[Layout('flux::layouts.print-layout-editor',[
        'subject' => 'Layout Editor',
    ])]
    public function render(): View
    {
        return view('flux::livewire.a4-page');
    }

}

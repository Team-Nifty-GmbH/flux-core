<?php

namespace FluxErp\View\Layouts;

use Closure;
use FluxErp\View\PageTitle;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Request;
use Illuminate\View\Component;

class App extends Component
{
    public function __construct(public ?string $title = null)
    {
        $this->title ??= resolve_static(PageTitle::class, 'forRoute', [Request::route()]);
    }

    public function render(): View|Closure|string
    {
        return view('flux::layouts.app');
    }
}

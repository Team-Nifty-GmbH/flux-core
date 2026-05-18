<?php

use Illuminate\Support\Facades\Blade;

it('renders the nuxbe-lightbox overlay markup', function (): void {
    $html = Blade::render('<x-nuxbe-lightbox />');

    expect($html)->toContain('nuxbe:lightbox:open.window');
    expect($html)->toContain('x-ref="content"');
    expect($html)->toContain('bg-black/80');
    expect($html)->toContain('keydown.escape.window');
});

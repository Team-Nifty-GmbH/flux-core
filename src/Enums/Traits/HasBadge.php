<?php

namespace FluxErp\Enums\Traits;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

trait HasBadge
{
    abstract public function color(): string;

    public function badge(): HtmlString
    {
        return new HtmlString(
            Blade::render(
                html_entity_decode('<x-badge :$text :$color />'),
                [
                    'color' => $this->color(),
                    'text' => __(Str::headline($this->value)),
                ]
            )
        );
    }
}

<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

enum PrintJobStatusEnum: string
{
    use EnumTrait;

    case Queued = 'queued';

    case Processing = 'processing';

    case Completed = 'completed';

    case Failed = 'failed';

    case Cancelled = 'cancelled';

    public function badge(): HtmlString
    {
        return new HtmlString(
            Blade::render(
                html_entity_decode('<x-badge :$text :$color />'),
                [
                    'color' => match ($this) {
                        self::Queued => 'gray',
                        self::Processing => 'amber',
                        self::Completed => 'emerald',
                        self::Failed => 'red',
                        self::Cancelled => 'gray',
                    },
                    'text' => __(Str::headline($this->value)),
                ]
            )
        );
    }
}

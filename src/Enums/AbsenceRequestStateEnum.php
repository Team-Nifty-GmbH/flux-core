<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

enum AbsenceRequestStateEnum: string
{
    use EnumTrait;

    case Approved = 'approved';

    case Pending = 'pending';

    case Rejected = 'rejected';

    case Revoked = 'revoked';

    public function badge(): HtmlString
    {
        return new HtmlString(
            Blade::render(
                html_entity_decode('<x-badge :$text :$color />'),
                [
                    'color' => match ($this) {
                        self::Pending => 'gray',
                        self::Approved => 'emerald',
                        self::Rejected => 'red',
                        self::Revoked => 'amber',
                    },
                    'text' => __(Str::headline($this->value)),
                ]
            )
        );
    }
}

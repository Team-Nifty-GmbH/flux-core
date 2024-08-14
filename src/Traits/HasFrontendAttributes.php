<?php

namespace FluxErp\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasFrontendAttributes
{
    use \TeamNiftyGmbH\DataTable\Traits\HasFrontendAttributes;

    public static function getLivewireComponentWidget(): string
    {
        $default = 'widgets.'.strtolower(class_basename(self::class));

        return livewire_component_exists($default) ? $default : 'widgets.generic';
    }

    public function avatarUrl(): Attribute
    {
        return Attribute::get(
            fn () => method_exists($this, 'getAvatarUrl') ? $this->getAvatarUrl() : null
        );
    }
}

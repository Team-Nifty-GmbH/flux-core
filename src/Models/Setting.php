<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;

class Setting extends FluxModel
{
    use HasPackageFactory;

    public static function get(string $property)
    {
        [$group, $name] = explode('.', $property);

        $setting = static::query()
            ->where('group', $group)
            ->where('name', $name)
            ->first('payload');

        return json_decode($setting->getAttribute('payload'));
    }

    protected function casts(): array
    {
        return [
            'locked' => 'boolean',
        ];
    }
}

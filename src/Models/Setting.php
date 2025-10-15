<?php

namespace FluxErp\Models;

class Setting extends FluxModel
{
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

<?php

namespace FluxErp\Traits;

trait HasEnums
{
    public static function getEnums(): array
    {
        $model = new static();

        return $model->getEnumConfigs();
    }

    private function getEnumConfigs(): array
    {
        $casts = $this->getCasts();

        $enums = [];

        foreach ($casts as $field => $enum) {
            if (! enum_exists($enum)) {
                continue;
            }

            $enums[$field] = $enum::values();
        }

        return $enums;
    }
}

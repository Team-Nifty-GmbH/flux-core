<?php

namespace FluxErp\Actions\Media;

use FluxErp\Models\Setting;

class CustomProperties
{
    public static function get(array $data, string $model): array
    {
        $settings = collect(Setting::query()
            ->where('key', 'media_custom_paths')
            ->first()
            ?->settings);

        $customProperties = $data['custom_properties'] ?? [];

        $modelSetting = $settings
            ->where('model', $model)
            ->first();

        if ($modelSetting) {
            foreach (($modelSetting['custom_properties'] ?? []) as $customProperty) {
                if (array_key_exists($customProperty, $data)) {
                    $customProperties += [$customProperty => (bool) $data[$customProperty]];
                } else {
                    $customProperties += [$customProperty => false];
                }
            }
        }

        return $customProperties;
    }
}

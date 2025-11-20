<?php

namespace FluxErp\Rulesets\Printer;

use FluxErp\Rulesets\FluxRuleset;

class GeneratePrinterBridgeConfigRuleset extends FluxRuleset
{
    protected static ?string $addValidationAttributesMethod = 'prepareForValidation';

    public function rules(): array
    {
        return [
            'instance_name' => 'required|string|max:255',
            'printer_check_interval' => 'required|integer|min:1',
            'job_check_interval' => 'required|integer|min:1',
            'api_port' => 'required|integer|min:1|max:65535',
            'reverb_disabled' => 'required|boolean',
            'force_regenerate' => 'required|boolean',
        ];
    }

    public static function prepareForValidation($attributes): array
    {
        $attributes['instance_name'] ??= 'default-instance';
        $attributes['printer_check_interval'] ??= 5;
        $attributes['job_check_interval'] ??= 2;
        $attributes['api_port'] ??= 8080;
        $attributes['reverb_disabled'] ??= ! config('reverb.app_id', config('broadcasting.connections.reverb.app_id'));
        $attributes['force_regenerate'] ??= false;

        return $attributes;
    }
}

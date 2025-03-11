<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum SalutationEnum: string
{
    use EnumTrait;

    public function gender(): string
    {
        return match ($this) {
            SalutationEnum::MRS => 'female',
            SalutationEnum::MR => 'male',
            default => 'neutral',
        };
    }

    public function salutation(object|array $address): string
    {
        $parameter = [
            'firstname' => data_get($address, 'firstname'),
            'lastname' => data_get($address, 'lastname'),
            'company' => data_get($address, 'company'),
        ];

        if (data_get($address, 'has_formal_salutation')) {
            return match ($this) {
                SalutationEnum::MRS => __('salutation.formal.mrs', $parameter),
                SalutationEnum::MR => __('salutation.formal.mr', $parameter),
                SalutationEnum::COMPANY => __('salutation.formal.company', $parameter),
                SalutationEnum::FAMILY => __('salutation.formal.family', $parameter),
                default => __('salutation.formal.no_salutation', $parameter),
            };
        } else {
            return match ($this) {
                SalutationEnum::MRS => __('salutation.informal.mrs', $parameter),
                SalutationEnum::MR => __('salutation.informal.mr', $parameter),
                SalutationEnum::COMPANY => __('salutation.informal.company', $parameter),
                SalutationEnum::FAMILY => __('salutation.informal.family', $parameter),
                default => __('salutation.informal.no_salutation', $parameter),
            };
        }
    }

    case COMPANY = 'company';

    case FAMILY = 'family';

    case MR = 'mr';

    case MRS = 'mrs';

    case NO_SALUTATION = 'no_salutation';
}

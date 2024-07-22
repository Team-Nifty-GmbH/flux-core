<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Models\Address;

enum SalutationEnum: string
{
    use EnumTrait;

    case MR = 'mr';

    case MRS = 'mrs';

    case COMPANY = 'company';

    case FAMILY = 'family';

    case NO_SALUTATION = 'no_salutation';

    public function salutation(Address $address): string
    {
        $parameter = [
            'firstname' => $address->firstname,
            'lastname' => $address->lastname,
            'company' => $address->company,
        ];

        if ($address->is_formal_salutation) {
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

    public function gender(): string
    {
        return match ($this) {
            SalutationEnum::MRS => 'female',
            SalutationEnum::MR => 'male',
            default => 'neutral',
        };
    }
}

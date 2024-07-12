<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Feature\BaseSetup as FeatureBaseSetup;

class BaseSetup extends FeatureBaseSetup
{
    protected function setUp(): void
    {
        parent::setUp();

        PriceList::factory()->create(['is_default' => true]);
        PaymentType::factory()->create(['is_default' => true]);
    }
}

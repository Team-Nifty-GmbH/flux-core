<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\PriceList;
use FluxErp\Tests\Feature\BaseSetup as FeatureBaseSetup;

class BaseSetup extends FeatureBaseSetup
{
    protected function setUp(): void
    {
        PriceList::factory()->create(['is_default' => true]);

        parent::setUp();
    }
}

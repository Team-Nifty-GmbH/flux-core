<?php

namespace FluxErp\RuleEngine\Scopes;

use Carbon\Carbon;
use FluxErp\Models\Contact;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;

class PriceScope extends RuleScope
{
    public function __construct(
        public ?Product $product = null,
        public ?Contact $contact = null,
        public ?PriceList $priceList = null,
        public ?int $quantity = null,
        ?Carbon $now = null,
    ) {
        parent::__construct($now);
    }
}

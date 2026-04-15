<?php

namespace FluxErp\RuleEngine\Scopes;

use Carbon\Carbon;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use Illuminate\Support\Collection;

class OrderScope extends RuleScope
{
    public function __construct(
        public Order $order,
        public Collection $positions,
        public ?Contact $contact = null,
        ?Carbon $now = null,
    ) {
        parent::__construct($now);
    }
}

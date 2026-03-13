<?php

namespace FluxErp\Traits;

trait CalculatesPositionAvailability
{
    protected function calculateMaxAmounts(array $positions, int $multiplier): array
    {
        return array_reduce(
            $positions,
            function (?array $carry, object $item) {
                $parentKey = array_find_key(
                    $carry ?? [],
                    fn (array $value) => ! is_null($item->origin_position_id)
                        && in_array(
                            $item->origin_position_id,
                            $value['all_ids'] ?? [$value['id']]
                        )
                );

                if (is_null($parentKey)) {
                    $carry[] = (array) $item;
                } else {
                    $carry[$parentKey]['all_ids'] = array_merge(
                        $carry[$parentKey]['all_ids'] ?? [$carry[$parentKey]['id']],
                        [$item->id]
                    );

                    // Direct children always reduce availability (use absolute value).
                    // Indirect children (e.g. Retoure-of-Anzahlung) use signed_amount
                    // so negative amounts correctly free up the parent's claimed amount.
                    $amount = $item->origin_position_id === $carry[$parentKey]['id']
                        ? (bccomp($item->signed_amount, '0') < 0
                            ? bcmul($item->signed_amount, '-1')
                            : $item->signed_amount)
                        : $item->signed_amount;

                    $carry[$parentKey]['signed_amount'] = bcsub(
                        $carry[$parentKey]['signed_amount'],
                        $amount
                    );
                }

                return $carry;
            }
        );
    }
}

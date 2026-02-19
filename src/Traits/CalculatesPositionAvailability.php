<?php

namespace FluxErp\Traits;

trait CalculatesPositionAvailability
{
    protected function calculateMaxAmounts(array $positions, int $multiplier): array
    {
        return array_reduce(
            $positions,
            function (?array $carry, object $item) use ($multiplier) {
                $parentKey = array_find_key(
                    $carry ?? [],
                    fn (array $value) => ! is_null($item->origin_position_id)
                        && in_array(
                            $item->origin_position_id,
                            [
                                $value['id'],
                                $value['origin_position_id'],
                            ]
                        )
                );

                if (is_null($parentKey)) {
                    $carry[] = (array) $item;
                } else {
                    $carry[$parentKey] = array_merge(
                        $carry[$parentKey],
                        [
                            'origin_position_id' => $item->id,
                            'signed_amount' => bcsub(
                                data_get($carry, $parentKey . '.signed_amount'),
                                bcmul($item->signed_amount, $multiplier)
                            ),
                        ]
                    );
                }

                return $carry;
            }
        );
    }
}

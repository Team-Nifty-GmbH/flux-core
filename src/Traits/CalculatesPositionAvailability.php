<?php

namespace FluxErp\Traits;

trait CalculatesPositionAvailability
{
    protected function calculateMaxAmounts(array $positions): array
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

                    $carry[$parentKey]['signed_amount'] = bcsub(
                        $carry[$parentKey]['signed_amount'],
                        $this->resolveChildAmount($item, $carry[$parentKey]['id'])
                    );
                }

                return $carry;
            }
        );
    }

    /**
     * Direct children always reduce availability (absolute value).
     * Indirect children (e.g. Retoure-of-Anzahlung) use signed_amount
     * so negative amounts correctly free up the parent's claimed amount.
     */
    protected function resolveChildAmount(object $item, int $rootId): string
    {
        $signedAmount = $item->signed_amount ?? '0';

        if ($item->origin_position_id !== $rootId) {
            return $signedAmount;
        }

        return bccomp($signedAmount, '0') === -1
            ? bcmul($signedAmount, '-1')
            : $signedAmount;
    }
}

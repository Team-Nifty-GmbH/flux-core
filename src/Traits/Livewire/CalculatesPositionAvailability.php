<?php

namespace FluxErp\Traits\Livewire;

trait CalculatesPositionAvailability
{
    protected function calculateMaxAmounts(array $allPositions, array $rootPositionIds): array
    {
        $maxAmounts = [];

        foreach ($rootPositionIds as $rootId) {
            $root = array_find($allPositions, fn (array $p): bool => data_get($p, 'id') === $rootId);
            if (! $root) {
                continue;
            }

            $consumed = '0';
            foreach (array_filter($allPositions, fn (array $p): bool => data_get($p, 'origin_position_id') === $rootId) as $child) {
                if (bccomp(data_get($child, 'signed_amount'), 0) === -1) {
                    $consumed = bcadd($consumed, bcabs(data_get($child, 'signed_amount')));
                } else {
                    $netConsumed = bcsub(
                        bcabs(data_get($child, 'signed_amount')),
                        $this->calculateReturnedAmount($allPositions, data_get($child, 'id'))
                    );

                    if (bccomp($netConsumed, 0) === 1) {
                        $consumed = bcadd($consumed, $netConsumed);
                    }
                }
            }

            $maxAmounts[] = [
                'id' => $rootId,
                'origin_position_id' => data_get($root, 'origin_position_id'),
                'signed_amount' => bcsub(bcabs(data_get($root, 'signed_amount')), $consumed),
            ];
        }

        return $maxAmounts;
    }

    protected function calculateReturnedAmount(array $allPositions, int $positionId): string
    {
        $returned = '0';

        foreach (array_filter($allPositions, fn (array $p): bool => data_get($p, 'origin_position_id') === $positionId) as $child) {
            $returned = bccomp(data_get($child, 'signed_amount'), 0) === -1
                ? bcadd($returned, bcabs(data_get($child, 'signed_amount')))
                : bcadd($returned, $this->calculateReturnedAmount($allPositions, data_get($child, 'id')));
        }

        return $returned;
    }
}

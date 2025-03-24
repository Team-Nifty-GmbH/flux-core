<?php

namespace FluxErp\Traits;

use Exception;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

trait HasSerialNumberRange
{
    public function getSerialNumber(string|array $types, ?int $clientId = null): static
    {
        $types = (array) $types;
        $clientId = $clientId ?? ($this->client_id ?? null);

        foreach ($types as $type) {
            if ($this->{$type}) {
                continue;
            }

            $query = resolve_static(SerialNumberRange::class, 'query')
                ->where('type', $type)
                ->where('model_type', app(static::class)->getMorphClass())
                ->where('client_id', $clientId);

            $store = false;
            if (ModelInfo::forModel($this)->attribute($type)) {
                $query->whereNull('model_id');
            } else {
                $store = true;
                $query->where('model_id', $this->id);
            }

            $serialNumberRange = $query->firstOrNew();

            if (! $serialNumberRange->exists) {
                try {
                    $serialNumberRange->fill([
                        'client_id' => $clientId,
                        'type' => $type,
                        'model_type' => app(static::class)->getMorphClass(),
                        'stores_serial_numbers' => $store,
                    ])->save();
                } catch (Exception) {
                    $serialNumberRange = $query->firstOrNew();
                }
            }

            if (! $serialNumberRange->is_pre_filled && ! $serialNumberRange->is_randomized) {
                $serialNumberRange = DB::transaction(function () use ($serialNumberRange) {
                    $serialNumberRange = resolve_static(SerialNumberRange::class, 'query')
                        ->whereKey($serialNumberRange->getKey())
                        ->lockForUpdate()
                        ->first();
                    $serialNumberRange->increment('current_number');

                    return $serialNumberRange;
                });
            } elseif ($serialNumberRange->is_randomized) {
                $serialNumberRange->current_number = Str::uuid()->toString();
            }

            $serialNumberRange->model_id = $this->getKey();
            $styled = $serialNumberRange->getCurrentStyled();

            if ($serialNumberRange->stores_serial_numbers) {
                $serialNumber = app(SerialNumber::class, [
                    'attributes' => [
                        'serial_number_range_id' => $serialNumberRange->id,
                        'serial_number' => $styled,
                    ],
                ]);
                $serialNumber->save();
            }

            $this->{$type} = $styled;
        }

        return $this;
    }
}

<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateCurrencyRequest;
use FluxErp\Models\Currency;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CurrencyService
{
    public function create(array $data): Currency
    {
        $currency = new Currency($data);
        $currency->save();

        return $currency;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateCurrencyRequest(),
        );

        foreach ($data as $item) {
            $currency = Currency::query()
                ->whereKey($item['id'])
                ->first();

            // Save new data to table.
            $currency->fill($item);
            $currency->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $currency->withoutRelations()->fresh(),
                additions: ['id' => $currency->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'currencies updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $currency = Currency::query()
            ->whereKey($id)
            ->first();

        if (! $currency) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'currency not found']
            );
        }

        // Don't delete if in use.
        if ($currency->countries()->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['country' => 'currency referenced by a country']
            );
        }

        // Rename unique columns on soft-delete.
        $currency->iso = $currency->iso . '___' . Hash::make(Str::uuid());
        $currency->save();
        $currency->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'currency deleted'
        );
    }

    public function initializeCurrencies(): void
    {
        $path = resource_path() . '/init-files/currencies.json';
        $json = json_decode(file_get_contents($path));

        if ($json->model === 'Currency') {
            $jsonCurrencies = $json->data;

            if ($jsonCurrencies) {
                foreach ($jsonCurrencies as $jsonCurrency) {
                    // Save to database.
                    Currency::query()
                        ->updateOrCreate([
                            'iso' => $jsonCurrency->iso,
                        ], [
                            'name' => $jsonCurrency->name,
                            'symbol' => $jsonCurrency->symbol,
                        ]);
                }
            }
        }
    }
}

<?php

use FluxErp\Enums\SalutationEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class() extends Migration
{
    public function up(): void
    {
        $labels = $this->labels();

        if (! $labels) {
            return;
        }

        DB::table('orders')
            ->select(['id', 'address_invoice', 'address_delivery'])
            ->orderBy('id')
            ->chunk(500, function (Collection $orders) use ($labels): void {
                foreach ($orders as $order) {
                    $changes = [];

                    foreach (['address_invoice', 'address_delivery'] as $column) {
                        $address = json_decode($order->{$column} ?? '', true);
                        $salutation = data_get($address, 'salutation');

                        if (! is_array($address)
                            || ! is_string($salutation)
                            || ! array_key_exists($salutation, $labels)
                        ) {
                            continue;
                        }

                        $address['salutation'] = $labels[$salutation];
                        $changes[$column] = json_encode($address);
                    }

                    if ($changes) {
                        DB::table('orders')
                            ->where('id', $order->id)
                            ->update($changes);
                    }
                }
            });
    }

    public function down(): void
    {
        //
    }

    protected function labels(): array
    {
        $values = array_map(
            fn (object $case): string => $case->value,
            resolve_static(SalutationEnum::class, 'cases')
        );

        $labels = [];

        foreach ($this->locales() as $locale) {
            foreach ($values as $value) {
                foreach ([Str::headline($value), $value] as $key) {
                    $label = trans($key, [], $locale);

                    if ($label !== $key && ! in_array($label, $values, true)) {
                        $labels[$label] = $value;
                    }
                }
            }
        }

        return $labels;
    }

    protected function locales(): array
    {
        return collect(glob(lang_path('*.json')))
            ->merge(glob(__DIR__ . '/../../lang/*.json'))
            ->map(fn (string $path): string => basename($path, '.json'))
            ->push(config('app.locale'), config('app.fallback_locale'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
};

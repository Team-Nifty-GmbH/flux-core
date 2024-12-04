<?php

/**
 * Blade Directives
 *
 * @method static bool canAction(string $action)
 * @method static void endCanAction()
 * @method static string extendFlux(string $view)
 */
if (! function_exists('format_number')) {
    function format_number(
        string|int|float|null $number,
        int $style = \NumberFormatter::DECIMAL,
        int $maxFractionDigits = 2,
        ?string $currencyCode = null
    ): float|bool|int|string|null {
        if (! is_numeric($number)) {
            return $number;
        }

        $numberFormatter = numfmt_create(app()->getLocale(), $style);
        numfmt_set_attribute($numberFormatter, \NumberFormatter::MAX_FRACTION_DIGITS, $maxFractionDigits);

        if ($style === \NumberFormatter::CURRENCY) {
            return numfmt_format_currency($numberFormatter, $number, $currencyCode);
        }

        return numfmt_format($numberFormatter, $number);
    }
}

if (! function_exists('exception_to_notifications')) {
    function exception_to_notifications(
        Exception $exception,
        Livewire\Component $component,
        bool $skipRender = true,
        ?string $description = null
    ): void {
        if (! method_exists($component, 'notification')) {
            throw new InvalidArgumentException('Component does not have a notification method.');
        }

        switch (true) {
            case method_exists($exception, 'errors') && $errors = $exception->errors():
            case method_exists($exception, 'getResponse')
            && $errors = data_get(
                json_decode($exception->getResponse()->getContent(), true),
                'errors',
                []
            ):
                foreach ($errors as $field => $messages) {
                    $title = array_map(
                        fn ($segment) => is_numeric($segment)
                            ? $segment + 1
                            : __(\Illuminate\Support\Str::headline($segment)),
                        explode('.', $field)
                    );

                    foreach (\Illuminate\Support\Arr::flatten($messages) as $message) {
                        $component->notification()->error(implode(' -> ', $title), __($message), $description);
                        $component->addError($field, __($message));
                    }
                }

                break;
            default:
                $component->notification()->error($exception->getMessage(), $description);
                $component->addError('', $exception->getMessage());
        }

        if (! $exception instanceof \Illuminate\Validation\ValidationException) {
            \Illuminate\Support\Facades\Log::error(
                $exception->getMessage(),
                [
                    'exception' => $exception,
                    'backtrace' => $exception->getTraceAsString(),
                ]);
        }

        if ($skipRender) {
            $component->skipRender();
        }
    }
}

if (! function_exists('cart')) {
    function cart(): \FluxErp\Models\Cart
    {
        return auth()
            ->user()
            ?->carts()
            ->current()
            ->with([
                'cartItems' => fn (\Illuminate\Database\Eloquent\Relations\HasMany $query) => $query->ordered(),
                'cartItems.product.coverMedia',
            ])
            ->withSum('cartItems', 'total')
            ->withSum('cartItems', 'total_net')
            ->withSum('cartItems', 'total_gross')
            ->first()
            ?? resolve_static(\FluxErp\Models\Cart::class, 'query')
                ->where('session_id', session()->id())
                ->current()
                ->with(['cartItems', 'cartItems.product.coverMedia'])
                ->withSum('cartItems', 'total')
                ->first()
            ?? \FluxErp\Actions\Cart\CreateCart::make()
                ->validate()
                ->execute();
    }
}

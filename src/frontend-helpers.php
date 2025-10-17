<?php

/**
 * Blade Directives
 *
 * @method static bool canAction(string $action)
 * @method static void endcanAction()
 */
if (! function_exists('exception_to_notifications')) {
    function exception_to_notifications(
        Exception $exception,
        Livewire\Component $component,
        bool $skipRender = true,
        ?string $description = null
    ): void {
        if (! method_exists($component, 'toast')) {
            throw new InvalidArgumentException('Component does not have a toast method.');
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
                            : __(Illuminate\Support\Str::headline($segment)),
                        explode('.', $field)
                    );

                    foreach (Illuminate\Support\Arr::flatten($messages) as $message) {
                        $component->toast()
                            ->error(implode(' -> ', $title), __($message), $description)
                            ->send();
                        $component->addError($field, __($message));
                    }
                }

                break;
            default:
                $component->toast()->error($exception->getMessage(), $description)->send();
                $component->addError('', $exception->getMessage());
        }

        if (
            ! $exception instanceof Illuminate\Validation\ValidationException
            && ! $exception instanceof Spatie\Permission\Exceptions\UnauthorizedException
        ) {
            report($exception);
        }

        if ($skipRender) {
            $component->skipRender();
        }
    }
}

if (! function_exists('cart')) {
    function cart(): FluxErp\Models\Cart
    {
        return auth()
            ->user()
            ?->carts()
            ->current()
            ->with([
                'cartItems' => fn (Illuminate\Database\Eloquent\Relations\HasMany $query) => $query->ordered(),
                'cartItems.product.coverMedia',
            ])
            ->withSum('cartItems', 'total')
            ->withSum('cartItems', 'total_net')
            ->withSum('cartItems', 'total_gross')
            ->first()
            ?? resolve_static(FluxErp\Models\Cart::class, 'query')
                ->where('session_id', session()->id())
                ->current()
                ->with(['cartItems', 'cartItems.product.coverMedia'])
                ->withSum('cartItems', 'total')
                ->first()
            ?? FluxErp\Actions\Cart\CreateCart::make()
                ->validate()
                ->execute();
    }
}

if (! function_exists('find_common_base_uri')) {
    function find_common_base_uri(
        array $navigation,
        string $childAttribute = 'children',
        string $uriAttribute = 'uri'
    ): ?string {
        $uris = array_column(data_get($navigation, $childAttribute, []), $uriAttribute);

        if (! $uris) {
            return null;
        }

        // Extract only the path part of the URLs
        $paths = array_map(fn (string $uri) => parse_url($uri, PHP_URL_PATH), $uris);

        // Find the common base path
        $basePath = array_shift($paths);

        foreach ($paths as $path) {
            while (! str_starts_with($path, $basePath)) {
                $basePath = dirname($basePath);
                if ($basePath === '/' || $basePath === '.') {
                    return '/';
                }
            }
        }

        return rtrim($basePath, '/') . '/';
    }
}

if (! function_exists('format_money')) {
    function format_money(
        string|int|float $amount,
        ?FluxErp\Models\Currency $currency = null,
        ?FluxErp\Models\Language $language = null
    ): string {
        return Illuminate\Support\Number::currency(
            bcround($amount, 2),
            $currency?->iso
                ?? resolve_static(FluxErp\Models\Currency::class, 'default')->iso,
            $language?->language_code
                ?? resolve_static(FluxErp\Models\Language::class, 'default')->language_code
        );
    }
}

<?php

namespace FluxErp\Mechanisms\FrontendAssets;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class SupportAutoInjectedAssets
{
    public static bool $forceAssetInjection = false;

    public static bool $hasRenderedFluxComponentThisRequest = false;

    public static function injectAssets(string $html, string $assetsHead, string $assetsBody): string
    {
        $html = Str::of($html);

        if ($html->test('/<\s*\/\s*head\s*>/i') && $html->test('/<\s*\/\s*body\s*>/i')) {
            return $html
                ->replaceMatches('/(<\s*\/\s*head\s*>)/i', $assetsHead . '$1')
                ->replaceMatches('/(<\s*\/\s*body\s*>)/i', $assetsBody . '$1')
                ->toString();
        }

        return $html
            ->replaceMatches('/(<\s*html(?:\s[^>])*>)/i', '$1' . $assetsHead)
            ->replaceMatches('/(<\s*\/\s*html\s*>)/i', $assetsBody . '$1')
            ->toString();
    }

    public static function forceAssetInjection(): void
    {
        static::$forceAssetInjection = true;
    }

    protected static function shouldInjectAssets(RequestHandled $handled): bool
    {
        if (! static::$forceAssetInjection && config('flux.inject_assets', true) === false) {
            return false;
        }

        $contentType = $handled->response->headers->get('content-type', '');
        if (! Str::contains($contentType, 'text/html')) {
            return false;
        }

        if (! method_exists($handled->response, 'status') || $handled->response->status() !== 200) {
            return false;
        }

        $frontendAssets = app(FrontendAssets::class);
        if ($frontendAssets->hasRenderedScripts) {
            return false;
        }

        return true;
    }

    public function boot(): void
    {
        $this->registerRequestHandledListener();
    }

    protected function registerRequestHandledListener(): void
    {
        Event::listen(RequestHandled::class, function (RequestHandled $handled): void {
            if (! static::shouldInjectAssets($handled)) {
                return;
            }

            $html = $handled->response->getContent();

            if (! Str::contains($html, '</html>')) {
                return;
            }

            $assetsHead = FrontendAssets::styles()->toHtml();
            $assetsBody = FrontendAssets::scripts()->toHtml();

            if ($assetsHead === '' && $assetsBody === '') {
                return;
            }

            $originalContent = $handled->response->original;
            $handled->response->setContent(
                static::injectAssets($html, $assetsHead, $assetsBody)
            );
            $handled->response->original = $originalContent;
        });
    }
}

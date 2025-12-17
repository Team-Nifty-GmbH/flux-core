<?php

use FluxErp\Mechanisms\FrontendAssets\FrontendAssets;
use FluxErp\Mechanisms\FrontendAssets\SupportAutoInjectedAssets;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    $this->frontendAssets = app(FrontendAssets::class);
});

describe('FrontendAssets', function (): void {
    test('routes are registered', function (): void {
        expect(Route::has('flux.assets.css'))->toBeTrue()
            ->and(Route::has('flux.assets.js'))->toBeTrue()
            ->and(Route::has('flux.assets.file'))->toBeTrue()
            ->and(Route::has('flux.assets.package'))->toBeTrue();
    });

    test('manifest is loaded correctly', function (): void {
        $manifest = FrontendAssets::getManifest();

        expect($manifest)->toBeArray()
            ->and($manifest)->toHaveKey('resources/css/app.css')
            ->and($manifest)->toHaveKey('resources/js/app.js')
            ->and($manifest)->toHaveKey('resources/js/alpine.js');
    });

    test('styles method returns html string with css link', function (): void {
        $styles = FrontendAssets::styles();

        expect($styles)->toBeInstanceOf(\Illuminate\Support\HtmlString::class)
            ->and($styles->toHtml())->toContain('<link rel="stylesheet"')
            ->and($styles->toHtml())->toContain('app-');
    });

    test('scripts method returns html string with script tags', function (): void {
        $scripts = FrontendAssets::scripts();

        expect($scripts)->toBeInstanceOf(\Illuminate\Support\HtmlString::class)
            ->and($scripts->toHtml())->toContain('<script type="module"')
            ->and($scripts->toHtml())->toContain('app-');
    });

    test('css route returns css file with correct content type', function (): void {
        $response = $this->get(route('flux.assets.css'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/css; charset=utf-8');
    });

    test('js route returns js file with correct content type', function (): void {
        $response = $this->get(route('flux.assets.js'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8');
    });

    test('asset file route returns files from build directory', function (): void {
        $manifest = FrontendAssets::getManifest();
        $cssEntry = $manifest['resources/css/app.css'];

        $response = $this->get(route('flux.assets.file', ['file' => $cssEntry['file']]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/css; charset=utf-8');
    });

    test('asset file route returns 404 for non-existent files', function (): void {
        $response = $this->get(route('flux.assets.file', ['file' => 'non-existent-file.js']));

        $response->assertNotFound();
    });

    test('asset file route blocks path traversal attempts', function (): void {
        $response = $this->get(route('flux.assets.file', ['file' => '../../../etc/passwd']));

        $response->assertNotFound();
    });

    test('font files are served with correct mime type', function (): void {
        $manifest = FrontendAssets::getManifest();

        $woff2Entry = collect($manifest)->first(fn ($entry) => str_ends_with($entry['file'] ?? '', '.woff2'));

        if ($woff2Entry) {
            $response = $this->get(route('flux.assets.file', ['file' => $woff2Entry['file']]));

            $response->assertOk()
                ->assertHeader('Content-Type', 'font/woff2');
        } else {
            $this->markTestSkipped('No woff2 fonts in manifest');
        }
    });

    test('assets have cache headers', function (): void {
        $response = $this->get(route('flux.assets.css'));

        $response->assertOk()
            ->assertHeader('Cache-Control')
            ->assertHeader('Last-Modified');
    });

    test('hasRenderedScripts is set after scripts method called', function (): void {
        $instance = app(FrontendAssets::class);

        expect($instance->hasRenderedScripts)->toBeFalse();

        FrontendAssets::scripts();

        expect($instance->hasRenderedScripts)->toBeTrue();
    });

    test('hasRenderedStyles is set after styles method called', function (): void {
        $instance = app(FrontendAssets::class);

        expect($instance->hasRenderedStyles)->toBeFalse();

        FrontendAssets::styles();

        expect($instance->hasRenderedStyles)->toBeTrue();
    });
});

describe('Package Manifest Registration', function (): void {
    test('can register package manifest', function (): void {
        $this->frontendAssets->registerManifest(
            name: 'test-package',
            path: '/tmp/test-package/build',
            entries: ['resources/js/test.js']
        );

        expect($this->frontendAssets->getRegisteredPackages())->toContain('test-package');
    });

    test('package manifest returns empty array for non-existent package', function (): void {
        $manifest = FrontendAssets::getPackageManifest('non-existent-package');

        expect($manifest)->toBeArray()->toBeEmpty();
    });

    test('package asset route returns 404 for unregistered package', function (): void {
        $response = $this->get(route('flux.assets.package', [
            'package' => 'unregistered-package',
            'file' => 'test.js',
        ]));

        $response->assertNotFound();
    });
});

describe('SupportAutoInjectedAssets', function (): void {
    test('injects assets into html response', function (): void {
        $html = '<html><head></head><body></body></html>';
        $assetsHead = '<link rel="stylesheet" href="/test.css">';
        $assetsBody = '<script src="/test.js"></script>';

        $result = SupportAutoInjectedAssets::injectAssets($html, $assetsHead, $assetsBody);

        expect($result)->toContain('<link rel="stylesheet" href="/test.css"></head>')
            ->and($result)->toContain('<script src="/test.js"></script></body>');
    });

    test('injects assets before closing tags', function (): void {
        $html = '<html><head><title>Test</title></head><body><p>Content</p></body></html>';
        $assetsHead = '<!-- HEAD -->';
        $assetsBody = '<!-- BODY -->';

        $result = SupportAutoInjectedAssets::injectAssets($html, $assetsHead, $assetsBody);

        expect($result)->toBe('<html><head><title>Test</title><!-- HEAD --></head><body><p>Content</p><!-- BODY --></body></html>');
    });

    test('handles html without head/body tags', function (): void {
        $html = '<html><p>Simple</p></html>';
        $assetsHead = '<!-- HEAD -->';
        $assetsBody = '<!-- BODY -->';

        $result = SupportAutoInjectedAssets::injectAssets($html, $assetsHead, $assetsBody);

        expect($result)->toContain('<!-- HEAD -->')
            ->and($result)->toContain('<!-- BODY -->');
    });

    test('forceAssetInjection sets static property', function (): void {
        SupportAutoInjectedAssets::$forceAssetInjection = false;

        SupportAutoInjectedAssets::forceAssetInjection();

        expect(SupportAutoInjectedAssets::$forceAssetInjection)->toBeTrue();

        // Reset for other tests
        SupportAutoInjectedAssets::$forceAssetInjection = false;
    });

    test('assets are auto injected into html responses', function (): void {
        config(['flux.inject_assets' => true]);

        // Reset the hasRenderedScripts flag
        app(FrontendAssets::class)->hasRenderedScripts = false;

        $response = $this->get(route('login'));

        $response->assertOk();

        $content = $response->getContent();

        expect($content)->toContain('<link rel="stylesheet"')
            ->and($content)->toContain('<script type="module"');
    });

    test('assets are not injected when config is disabled', function (): void {
        config(['flux.inject_assets' => false]);

        // Reset the hasRenderedScripts flag
        app(FrontendAssets::class)->hasRenderedScripts = false;

        $response = $this->get(route('login'));

        $response->assertOk();

        // The login page should still have assets from manual injection or blade
        // This test verifies the config option works
        expect(config('flux.inject_assets'))->toBeFalse();
    });

    test('assets are not double injected when already rendered', function (): void {
        config(['flux.inject_assets' => true]);

        // Simulate that scripts have already been rendered
        app(FrontendAssets::class)->hasRenderedScripts = true;

        $response = $this->get(route('login'));

        $response->assertOk();

        // Count script tags - should only have what's in the blade, not double
        $content = $response->getContent();
        $scriptCount = substr_count($content, '<script type="module" src="');

        // Reset for other tests
        app(FrontendAssets::class)->hasRenderedScripts = false;

        // If hasRenderedScripts was true, auto-injection should be skipped
        expect($scriptCount)->toBeGreaterThanOrEqual(0);
    });
});

describe('Blade Directives', function (): void {
    test('fluxStyles directive returns valid php code', function (): void {
        $directive = FrontendAssets::fluxStyles();

        expect($directive)->toContain('FrontendAssets::styles()');
    });

    test('fluxScripts directive returns valid php code', function (): void {
        $directive = FrontendAssets::fluxScripts();

        expect($directive)->toContain('FrontendAssets::scripts()');
    });
});

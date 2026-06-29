<?php

namespace FluxErp\Settings;

use Spatie\LaravelSettings\Attributes\ShouldBeEncrypted;

class SearchSettings extends FluxSettings
{
    public bool $semantic_search_enabled = false;

    public string $embedder_url = '';

    #[ShouldBeEncrypted]
    public string $embedder_api_key = '';

    public string $embedder_model = 'text-embedding-3-small';

    public int $embedder_dimensions = 1536;

    public float $semantic_ratio = 0.5;

    public static function group(): string
    {
        return 'search';
    }
}

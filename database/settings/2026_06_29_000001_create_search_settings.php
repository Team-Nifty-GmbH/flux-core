<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('search.semantic_search_enabled', false);
        $this->migrator->add('search.embedder_url', '');
        $this->migrator->addEncrypted('search.embedder_api_key', '');
        $this->migrator->add('search.embedder_model', 'text-embedding-3-small');
        $this->migrator->add('search.embedder_dimensions', 1536);
        $this->migrator->add('search.semantic_ratio', 0.5);
    }

    public function down(): void
    {
        $this->migrator->delete('search.semantic_search_enabled');
        $this->migrator->delete('search.embedder_url');
        $this->migrator->delete('search.embedder_api_key');
        $this->migrator->delete('search.embedder_model');
        $this->migrator->delete('search.embedder_dimensions');
        $this->migrator->delete('search.semantic_ratio');
    }
};

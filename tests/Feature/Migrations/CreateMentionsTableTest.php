<?php

use Illuminate\Support\Facades\Schema;

it('creates the mentions table with the expected columns and indexes', function (): void {
    expect(Schema::hasTable('mentions'))->toBeTrue();

    expect(Schema::hasColumns('mentions', [
        'id', 'mention_source_type', 'mention_source_id',
        'mention_target_type', 'mention_target_id',
        'mention_type', 'user_id', 'created_at',
    ]))->toBeTrue();

    $indexes = collect(Schema::getIndexes('mentions'))->pluck('name')->all();

    expect($indexes)->toContain('mentions_mention_source_type_mention_source_id_index');
    expect($indexes)->toContain('mentions_mention_target_type_mention_target_id_created_at_index');
    expect($indexes)->toContain('mentions_user_id_created_at_index');
});

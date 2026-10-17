<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    $this->migration = require __DIR__
        . '/../../../database/migrations/2026_10_15_000001_widen_mail_header_columns_on_communications_table.php';

    Schema::table('communications', function (Blueprint $table): void {
        $table->dropIndex(['message_id']);
    });

    Schema::table('communications', function (Blueprint $table): void {
        $table->string('message_id')->nullable()->change();
        $table->string('subject')->nullable()->change();
    });
});

test('the migration replaces a message_id index with a legacy name', function (): void {
    Schema::table('communications', function (Blueprint $table): void {
        $table->index('message_id', 'mail_messages_message_id_index');
    });

    $this->migration->up();

    expect(Schema::hasIndex('communications', 'mail_messages_message_id_index'))->toBeFalse()
        ->and(Schema::hasIndex('communications', 'communications_message_id_index'))->toBeTrue()
        ->and(Schema::getColumnType('communications', 'subject'))->toBe('text');
});

test('the migration runs when the message_id index is missing', function (): void {
    $this->migration->up();

    expect(Schema::hasIndex('communications', 'communications_message_id_index'))->toBeTrue()
        ->and(Schema::getColumnType('communications', 'subject'))->toBe('text');
});

test('the migration runs again on an already widened table', function (): void {
    $this->migration->up();
    $this->migration->up();

    expect(Schema::hasIndex('communications', 'communications_message_id_index'))->toBeTrue();
});

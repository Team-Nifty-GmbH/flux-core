<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->foreignId('document_type_id')
                ->nullable()
                ->after('collection_name')
                ->constrained('document_types')
                ->restrictOnDelete();
            $table->date('document_date')->nullable()->after('document_type_id');
            $table->date('retain_until')->nullable()->after('document_date')->index();
            $table->string('review_state')->nullable()->after('retain_until');
            $table->text('review_note')->nullable()->after('review_state');
            $table->timestamp('reviewed_at')->nullable()->after('review_note');
            $table->string('reviewed_by')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('document_type_id');
            $table->dropColumn([
                'document_date',
                'retain_until',
                'review_state',
                'review_note',
                'reviewed_at',
                'reviewed_by',
            ]);
        });
    }
};

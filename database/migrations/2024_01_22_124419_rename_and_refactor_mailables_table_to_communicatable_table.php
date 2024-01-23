<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('mailables', 'communicatable');

        Schema::table('communicatable', function (Blueprint $table) {
            $table->dropUnique('mailables_ids_type_unique');
            $table->dropForeign('mailables_mail_message_id_foreign');
            $table->dropIndex('mailables_mail_message_id_foreign');
            $table->dropIndex('mailables_mailable_type_mailable_id_index');

            $table->renameColumn('mailable_type', 'communicatable_type');
            $table->renameColumn('mailable_id', 'communicatable_id');
            $table->renameColumn('mail_message_id', 'communication_id');

            $table->unique(
                ['communicatable_type', 'communicatable_id', 'communication_id'],
                'communicatable_type_id_communication_id_unique'
            );
            $table->foreign('communication_id')
                ->references('id')
                ->on('communications')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::rename('communicatable', 'mailables');

        Schema::table('mailables', function (Blueprint $table) {
            $table->dropUnique('communicatable_type_id_communication_id_unique');
            $table->dropForeign('communicatable_communication_id_foreign');
            $table->dropIndex('communicatable_communication_id_foreign');

            $table->renameColumn('communicatable_type', 'mailable_type');
            $table->renameColumn('communicatable_id', 'mailable_id');
            $table->renameColumn('communication_id', 'mail_message_id');

            $table->unique(['mailable_type', 'mailable_id'], 'mailables_ids_type_unique');
            $table->index(['mailable_type', 'mailable_id']);
            $table->foreign('mail_message_id')
                ->references('id')
                ->on('communications')
                ->cascadeOnDelete();
        });
    }
};

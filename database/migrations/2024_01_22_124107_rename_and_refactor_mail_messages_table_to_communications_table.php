<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::rename('mail_messages', 'communications');

        Schema::table('communications', function (Blueprint $table): void {
            $table->unsignedBigInteger('mail_account_id')->nullable()->change();
            $table->unsignedBigInteger('mail_folder_id')->nullable()->change();

            $table->string('communication_type_enum')->nullable()->after('bcc');
            $table->softDeletes()->after('updated_at');

            $table->renameIndex('mail_messages_mail_account_id_foreign', 'communications_mail_account_id_foreign');
            $table->renameIndex('mail_messages_mail_folder_id_foreign', 'communications_mail_folder_id_foreign');
        });

        $this->migrateCommunicationsTable();

        Schema::table('communications', function (Blueprint $table): void {
            $table->string('communication_type_enum')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        $this->rollbackCommunicationsTable();

        Schema::table('communications', function (Blueprint $table): void {
            $table->unsignedBigInteger('mail_account_id')->nullable(false)->change();
            $table->unsignedBigInteger('mail_folder_id')->nullable(false)->change();

            $table->dropColumn([
                'communication_type_enum',
                'deleted_at',
            ]);

            $table->renameIndex('communications_mail_account_id_foreign', 'mail_messages_mail_account_id_foreign');
            $table->renameIndex('communications_mail_folder_id_foreign', 'mail_messages_mail_folder_id_foreign');
        });

        Schema::rename('communications', 'mail_messages');
    }

    private function migrateCommunicationsTable(): void
    {
        DB::table('communications')
            ->update(['communication_type_enum' => 'mail']);
    }

    private function rollbackCommunicationsTable(): void
    {
        DB::table('communications')
            ->whereNull('mail_account_id')
            ->orWhereNull('mail_folder_id')
            ->delete();
    }
};

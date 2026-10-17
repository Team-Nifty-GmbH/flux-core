<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        foreach (Schema::getIndexes('communications') as $index) {
            if ($index['columns'] === ['message_id']) {
                Schema::table('communications', function (Blueprint $table) use ($index): void {
                    $table->dropIndex($index['name']);
                });
            }
        }

        Schema::table('communications', function (Blueprint $table): void {
            $table->string('message_id', 998)->nullable()->change();
            $table->text('subject')->nullable()->change();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('CREATE INDEX communications_message_id_index ON communications (message_id(768))');
        } else {
            Schema::table('communications', function (Blueprint $table): void {
                $table->index('message_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('communications', function (Blueprint $table): void {
            $table->dropIndex(['message_id']);
        });

        Schema::table('communications', function (Blueprint $table): void {
            $table->string('message_id')->nullable()->index()->change();
            $table->string('subject')->nullable()->change();
        });
    }
};

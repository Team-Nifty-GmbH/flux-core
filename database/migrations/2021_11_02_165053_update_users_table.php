<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class UpdateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('language_id')->after('uuid');
            $table->boolean('is_active')->default(true)->after('user_code');
            $table->timestamp('created_at')->nullable()
                ->comment('A timestamp reflecting the time of record-creation.');
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.');
            $table->timestamp('updated_at')->nullable()
                ->comment('A timestamp reflecting the time of the last change for this record.');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.');
            $table->timestamp('deleted_at')->nullable()
                ->comment('A timestamp reflecting the time of record-deletion.');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.');

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });

        $this->migrateUserLanguage();

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_language_id_foreign');
            $table->dropForeign('users_created_by_foreign');
            $table->dropForeign('users_updated_by_foreign');
            $table->dropForeign('users_deleted_by_foreign');

            $table->dropColumn([
                'language_id',
                'is_active',
                'created_by',
                'updated_by',
                'deleted_at',
                'deleted_by',
            ]);
        });
    }

    private function migrateUserLanguage()
    {
        $users = DB::table('users')->exists();

        if ($users) {
            $language = DB::table('languages')->exists();
            if (! $language) {
                DB::table('languages')
                    ->insert([
                        'uuid' => Uuid::uuid4()->toString(),
                        'name' => config('app.locale'),
                    ]);
            }

            DB::table('users')
                ->update([
                    'language_id' => DB::table('languages')->first()->id,
                ]);
        }
    }
}

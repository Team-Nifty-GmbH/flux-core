<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mail_accounts', function (Blueprint $table): void {
            $table->string('protocol')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->text('password')->nullable()->change();
            $table->string('host')->nullable()->change();
            $table->integer('port')->nullable()->change();
            $table->string('encryption')->nullable()->change();

            $table->text('smtp_password')->nullable()->change();
            $table->string('smtp_host')->nullable()->change();

            $table->string('name')->after('id')->nullable();
            $table->string('smtp_from_name')->nullable()->after('smtp_email');
            $table->string('smtp_reply_to')->nullable()->after('smtp_from_name');
        });

        $this->fillNameField();

        Schema::table('mail_accounts', function (Blueprint $table): void {
            $table->string('name')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('mail_accounts', function (Blueprint $table): void {
            $table->string('protocol')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->text('password')->nullable(false)->change();
            $table->string('host')->nullable(false)->change();
            $table->integer('port')->nullable(false)->change();
            $table->string('encryption')->nullable(false)->change();

            $table->text('smtp_password')->nullable(false)->change();
            $table->string('smtp_host')->nullable(false)->change();

            $table->dropColumn(['name', 'smtp_from_name', 'smtp_reply_to']);
        });
    }

    private function fillNameField(): void
    {
        DB::table('mail_accounts')
            ->whereNull('name')
            ->update([
                'name' => DB::raw('COALESCE(email, smtp_email)'),
            ]);
    }
};

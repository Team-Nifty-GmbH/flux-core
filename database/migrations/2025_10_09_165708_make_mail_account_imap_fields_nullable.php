<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mail_accounts', function (Blueprint $table): void {
            $table->string('protocol')->nullable()->default('imap')->change();
            $table->string('email')->nullable()->change();
            $table->text('password')->nullable()->change();
            $table->string('host')->nullable()->change();
            $table->integer('port')->nullable()->change();
            $table->string('encryption')->nullable()->change();

            $table->integer('smtp_port')->nullable()->change();

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
        DB::table('mail_accounts')
            ->where(function (Builder $query): void {
                $query->whereNull('protocol')
                    ->orWhereNull('email')
                    ->orWhereNull('password')
                    ->orWhereNull('host')
                    ->orWhereNull('port')
                    ->orWhereNull('encryption')
                    ->orWhereNull('smtp_port');
            })
            ->delete();

        Schema::table('mail_accounts', function (Blueprint $table): void {
            $table->string('protocol')->nullable(false)->default('imap')->change();
            $table->string('email')->nullable(false)->change();
            $table->text('password')->nullable(false)->change();
            $table->string('host')->nullable(false)->change();
            $table->integer('port')->nullable(false)->default(993)->change();
            $table->string('encryption')->nullable(false)->default('ssl')->change();

            $table->integer('smtp_port')->nullable(false)->default(587)->change();

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

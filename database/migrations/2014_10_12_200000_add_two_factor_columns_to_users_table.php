<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('two_factor_secret')
                ->after('password')
                ->nullable();

            $table->text('two_factor_recovery_codes')
                ->after('two_factor_secret')
                ->nullable();

            $table->string('firstname')->after('email');
            $table->string('lastname')->after('firstname');
            $table->dropColumn('name');

            $table->timestamp('two_factor_confirmed_at')
                ->after('two_factor_recovery_codes')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('name')->after('email');
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'firstname',
                'lastname',
                'two_factor_confirmed_at',
            ]);
        });
    }
};

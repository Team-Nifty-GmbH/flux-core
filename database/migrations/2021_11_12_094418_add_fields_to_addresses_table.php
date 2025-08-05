<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToAddressesTable extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->string('salutation')->nullable()->after('company');
            $table->string('zip')->nullable()->after('lastname');
            $table->string('city')->nullable()->after('zip');
            $table->string('street')->nullable()->after('city');
            $table->string('url')->nullable()->after('street');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropColumn(['salutation', 'zip', 'city', 'street', 'url']);
        });
    }
}

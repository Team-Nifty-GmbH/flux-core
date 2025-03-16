<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryIdOnClientsTable extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->unsignedBigInteger('country_id');

            $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropForeign('clients_country_id_foreign');

            $table->dropColumn('country_id');
        });
    }
}

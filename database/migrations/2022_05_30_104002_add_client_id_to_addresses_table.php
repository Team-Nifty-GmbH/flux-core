<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->after('uuid')
                ->comment('A unique identifier number for the table clients.');
        });

        $this->migrateClientId();

        Schema::table('addresses', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign('addresses_client_id_foreign');

            $table->dropColumn('client_id');
        });
    }

    private function migrateClientId()
    {
        $clientId = DB::table('clients')
            ->first()
            ?->id;

        if (! $clientId && DB::table('addresses')->exists()) {
            $countryId = DB::table('countries')
                ->first()
                ->id;

            $clientId = DB::table('clients')->insertGetId([
                'uuid' => Uuid::uuid4(),
                'country_id' => $countryId,
                'name' => 'Client created by migration',
                'client_code' => 'Migration',
            ]);
        }

        DB::table('addresses')
            ->update([
                'client_id' => $clientId,
            ]);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('authenticatable_type')->after('uuid');
            $table->unsignedBigInteger('authenticatable_id')->after('authenticatable_type');

            $table->string('model_type')->nullable()->after('address_id');
            $table->unsignedBigInteger('model_id')->nullable()->after('model_type');

            $table->unsignedBigInteger('ticket_type_id')->nullable()->after('model_id');

            $table->index(['authenticatable_type', 'authenticatable_id']);
            $table->index(['model_type', 'model_id']);
            $table->foreign('ticket_type_id')->references('id')->on('ticket_types');
        });

        $this->migrateAddressId();

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign('tickets_address_id_foreign');
            $table->dropColumn('address_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')->after('uuid');

            $table->dropIndex('tickets_model_type_model_id_index');
            $table->dropForeign('tickets_ticket_type_id_foreign');

            $table->dropColumn([
                'model_type',
                'model_id',
                'ticket_type_id',
            ]);
        });

        $this->rollbackAddressId();

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreign('address_id')->references('id')->on('addresses');

            $table->dropIndex('tickets_authenticatable_type_authenticatable_id_index');

            $table->dropColumn([
                'authenticatable_type',
                'authenticatable_id',
            ]);
        });
    }

    private function migrateAddressId(): void
    {
        $addressClass = str_replace('\\', '\\\\', FluxErp\Models\Address::class);
        DB::statement('UPDATE tickets SET
                   authenticatable_type = ' . '\'' . $addressClass . '\'' . ',
                   authenticatable_id = address_id'
        );
    }

    private function rollbackAddressId(): void
    {
        $addressClass = str_replace('\\', '\\\\', FluxErp\Models\Address::class);
        DB::statement('UPDATE tickets SET
                   address_id = authenticatable_id
               WHERE authenticatable_type = ' . '\'' . $addressClass . '\''
        );
    }
};

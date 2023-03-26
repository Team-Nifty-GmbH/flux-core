<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->string('model_type')->after('uuid');
            $table->unsignedBigInteger('model_id')->nullable()->after('model_type');
            $table->string('type')->after('model_id');
            $table->foreignId('client_id')->nullable()->after('uuid')->constrained('clients');

            $table->boolean('stores_serial_numbers')->default(false)->after('description')
                ->comment('A flag indicating whether this range creates a new serial_numbers record.');
            $table->boolean('is_randomized')->default(false)->after('description')
                ->comment('A flag indicating whether this range generates a random serial number.');
            $table->boolean('is_pre_filled')->default(false)->after('description')
                ->comment('A flag to indicate if the serialnumber is picked from the serial_numbers table.');

            $table->integer('length')->nullable()->after('description')
                ->comment('The length of the serial number.'
                . ' The Serialnumber will be padded with leading zeros.');
        });

        DB::statement('UPDATE serial_number_ranges SET model_type = "FluxErp\\\\Models\\\\Product", model_id = product_id');

        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');

            $table->unique(['model_type', 'model_id', 'type', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->dropForeign(['client_id']);

            $table->unsignedBigInteger('product_id')->after('uuid');
        });

        Schema::table('serial_numbers', function (Blueprint $table) {
            $table->dropForeign(['serial_number_range_id']);
            $table->foreign('serial_number_range_id')
                ->references('id')
                ->on('serial_number_ranges')
                ->nullOnDelete();
        });

        DB::statement('DELETE FROM serial_number_ranges WHERE model_type != "FluxErp\\\\Models\\\\Product"');
        DB::statement('UPDATE serial_number_ranges SET product_id = model_id WHERE model_type = "FluxErp\\\\Models\\\\Product"');

        Schema::table('serial_numbers', function (Blueprint $table) {
            $table->dropForeign(['serial_number_range_id']);
            $table->foreign('serial_number_range_id')
                ->references('id')
                ->on('serial_number_ranges');
        });

        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products');
            $table->dropColumn([
                'type',
                'model_type',
                'model_id',
                'client_id',
                'stores_serial_numbers',
                'is_randomized',
                'is_pre_filled',
                'length',
            ]);
        });
    }
};

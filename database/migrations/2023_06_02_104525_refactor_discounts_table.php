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
        Schema::table('discounts', function (Blueprint $table): void {
            $table->string('model_type')->after('uuid');
            $table->unsignedBigInteger('model_id')->after('model_type');
            $table->timestamp('from')->nullable()->after('discount');
            $table->timestamp('till')->nullable()->after('from');

            $table->index(['model_type', 'model_id']);
        });

        $this->migrateDiscounts();

        Schema::table('discounts', function (Blueprint $table): void {
            $table->dropForeign(['order_position_id']);
            $table->dropColumn('order_position_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table): void {
            $table->unsignedBigInteger('order_position_id')->nullable()->after('uuid');
        });

        $this->rollbackDiscounts();

        Schema::table('discounts', function (Blueprint $table): void {
            $table->dropIndex('discounts_model_type_model_id_index');
            $table->dropColumn([
                'model_type',
                'model_id',
                'from',
                'till',
            ]);

            $table->foreign('order_position_id')->references('id')->on('order_positions');
        });
    }

    private function migrateDiscounts(): void
    {
        DB::statement('
            UPDATE discounts 
            SET model_id = order_position_id, 
                model_type = \'FluxErp\\\\Models\\\\OrderPosition\''
        );
    }

    private function rollbackDiscounts(): void
    {
        DB::statement('
            UPDATE discounts 
            SET order_position_id = model_id 
            WHERE model_type = \'FluxErp\\\\Models\\\\OrderPosition\''
        );
    }
};

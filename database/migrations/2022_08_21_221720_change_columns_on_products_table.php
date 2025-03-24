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
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['locked_by_user_id', 'is_force_active', 'is_force_inactive']);
            $table->renameColumn('option_bundle_type', 'is_pre_packed');
            $table->renameColumn('is_active_always_export_in_web_shop', 'is_nos');
            $table->unsignedBigInteger('client_id')->after('uuid');

            $table->decimal('dimension_height_mm', 40, 10)->nullable()->after('description');
            $table->decimal('dimension_width_mm', 40, 10)->nullable()->after('description');
            $table->decimal('dimension_length_mm', 40, 10)->nullable()->after('description');
            $table->decimal('weight_gram', 40, 10)->nullable()->after('description');
        });

        $this->migrateClientId();

        Schema::table('products', function (Blueprint $table): void {
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->text('locked_by_user_id')->nullable()->after('is_pre_packed');
            $table->boolean('is_force_active')->default(false)
                ->after('is_active_export_to_web_shop');
            $table->boolean('is_force_inactive')->default(false)->after('is_force_active');
            $table->renameColumn('is_pre_packed', 'option_bundle_type');
            $table->renameColumn('is_nos', 'is_active_always_export_in_web_shop');
            $table->dropForeign('products_client_id_foreign');
            $table->dropColumn(
                [
                    'client_id',
                    'dimension_width_mm',
                    'dimension_height_mm',
                    'dimension_length_mm',
                    'weight_gram',
                ]
            );
        });
    }

    private function migrateClientId(): void
    {
        $clientId = DB::table('clients')
            ->first()
            ?->id;

        if ($clientId) {
            DB::statement('UPDATE products SET client_id = ' . $clientId);
        }
    }
};

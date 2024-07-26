<?php

use FluxErp\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->migrateCategorizablesTable();

        Schema::table('category_product', function (Blueprint $table) {
            $table->dropIfExists();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('product_id');

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('product_id')->references('id')->on('products');
            $table->primary(['category_id', 'product_id']);
        });

        $this->rollbackCategorizablesTable();
    }

    private function migrateCategorizablesTable()
    {
        DB::statement('INSERT INTO categorizables(category_id, categorizable_type, categorizable_id)
            SELECT category_id, \'' . trim(
            json_encode(Product::class, JSON_UNESCAPED_SLASHES), '"'
        ) . '\', product_id
            FROM category_product'
        );
    }

    private function rollbackCategorizablesTable()
    {
        DB::statement('INSERT INTO category_product(category_id, product_id)
            SELECT category_id, categorizable_id
            FROM categorizables WHERE categorizable_type = \'' . trim(
            json_encode(Product::class, JSON_UNESCAPED_SLASHES), '"'
        ) . '\''
        );
    }
};

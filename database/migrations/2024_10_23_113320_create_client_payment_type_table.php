<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('client_payment_type', function (Blueprint $table) {
            $table->id('pivot_id');
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
        });

        DB::insert('INSERT INTO client_payment_type (client_id, payment_type_id) SELECT client_id, id FROM payment_types');

        Schema::table('payment_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_types', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable();
        });

        DB::statement('UPDATE payment_types p JOIN client_payment_type cp ON p.id = cp.payment_type_id SET p.client_id = cp.client_id');

        Schema::table('payment_types', function (Blueprint $table) {
            $table->foreign('client_id')->on('clients')->references('id')->cascadeOnDelete();
        });

        Schema::dropIfExists('client_payment_type');
    }
};

<?php

use FluxErp\Enums\SalutationEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->boolean('has_formal_salutation')->default(true)->after('password');
        });

        $this->migrateSalutations();
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('has_formal_salutation');
        });
    }

    private function migrateSalutations(): void
    {
        foreach (SalutationEnum::cases() as $salutation) {
            DB::table('addresses')
                ->where('salutation', __($salutation->value))
                ->update([
                    'salutation' => $salutation->value,
                ]);
        }
    }
};

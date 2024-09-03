<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('schedules')
            ->where('class', 'FluxErp\Jobs\ProcessSubscriptionOrderJob')
            ->update([
                'name' => 'ProcessSubscriptionOrder',
                'class' => 'FluxErp\Invokable\ProcessSubscriptionOrder',
                'type' => 'invokable',
                'parameters' => DB::raw("
                    JSON_SET(
                        '{}',
                        '$.orderId',
                        JSON_VALUE(parameters, '$.order'),
                        '$.orderTypeId',
                        JSON_VALUE(parameters, '$.orderType')
                    )
                "),
            ]);
    }

    public function down(): void {}
};

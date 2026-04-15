<?php

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $setting = DB::table('settings')
            ->where('group', 'mail')
            ->where('name', 'password')
            ->first();

        if (is_null($setting)) {
            return;
        }

        $payload = json_decode($setting->payload, true);

        if (is_null($payload)) {
            return;
        }

        try {
            Crypt::decryptString($payload);

            return;
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            // Not encrypted yet, proceed
        }

        DB::table('settings')
            ->where('id', $setting->id)
            ->update([
                'payload' => json_encode(Crypt::encryptString($payload)),
            ]);
    }
};

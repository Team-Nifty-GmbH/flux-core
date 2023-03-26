<?php

namespace FluxErp\Console\Commands\Init;

use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Services\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InitUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiates Users and fills table with data.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $admin = User::query()->where('user_code', 'admin')->firstOrNew();

        if (! $admin->exists) {
            $password = $this->secret(__('Enter password for user Admin'));
            if (! $password) {
                $password = Str::random();
                $this->info('admin Password: ' . $password);
            }

            $languageId = Language::query()
                ->where('language_code', config('tnc.defaults.locale'))
                ->first()
                ?->id;

            $admin->fill(
                [
                    'user_code' => 'admin',
                    'email' => 'admin@admin.de',
                    'firstname' => 'admin',
                    'lastname' => 'admin',
                    'password' => $password,
                    'is_active' => true,
                    'language_id' => $languageId ?? Language::query()->first()->id,
                ]
            );
            $admin->save();
            $admin->assignRole('Super Admin');
        }

        $userController = new UserService();
        $userController->initializeUsers();

        $this->info('Users initiated!');
    }
}

<?php

namespace FluxErp\Livewire\Forms;

use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Rule;
use Livewire\Form;

class DbForm extends Form
{
    #[Rule('required|string')]
    public ?string $database = null;

    #[Rule('required|string')]
    public ?string $host = null;

    #[Rule('nullable|string')]
    public ?string $password = null;

    #[Rule('required|integer')]
    public int $port = 3306;

    #[Rule('required|string')]
    public ?string $username = null;

    public function validate($rules = null, $messages = [], $attributes = []): void
    {
        parent::validate($rules, $messages, $attributes);

        DB::purge('mysql');
        config([
            'database.connections.mysql.host' => $this->host,
            'database.connections.mysql.port' => $this->port,
            'database.connections.mysql.database' => $this->database,
            'database.connections.mysql.username' => $this->username,
            'database.connections.mysql.password' => $this->password,
        ]);

        try {
            DB::connection('mysql')->getPdo();
        } catch (Exception $e) {
            $this->addError(null, $e->getMessage());
        }

        DB::purge('mysql');
    }
}

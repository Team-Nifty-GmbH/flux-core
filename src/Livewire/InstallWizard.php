<?php

namespace FluxErp\Livewire;

use FluxErp\Actions\OrderType\CreateOrderType;
use FluxErp\Actions\PaymentType\CreatePaymentType;
use FluxErp\Actions\PriceList\CreatePriceList;
use FluxErp\Actions\Warehouse\CreateWarehouse;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Events\InstallProcessOutputEvent;
use FluxErp\Jobs\InstallWizard\CommandJob;
use FluxErp\Livewire\Forms\ClientForm;
use FluxErp\Livewire\Forms\CurrencyForm;
use FluxErp\Livewire\Forms\DbForm;
use FluxErp\Livewire\Forms\LanguageForm;
use FluxErp\Livewire\Forms\PaymentTypeForm;
use FluxErp\Livewire\Forms\UserForm;
use FluxErp\Livewire\Forms\VatRateForm;
use FluxErp\Models\Language;
use FluxErp\Models\OrderType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Models\Warehouse;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use NotificationChannels\WebPush\VapidKeysGenerateCommand;
use Throwable;

class InstallWizard extends Component
{
    public ?string $batchId = null;

    public DbForm $dbForm;

    public LanguageForm $languageForm;

    public CurrencyForm $currencyForm;

    public ClientForm $clientForm;

    public UserForm $userForm;

    public VatRateForm $vatRateForm;

    public array $vatRates = [];

    public PaymentTypeForm $paymentTypeForm;

    public int $step = 0;

    #[Url]
    public bool $databaseConnectionSuccessful = false;

    #[Url]
    public bool $requestRefresh = false;

    public function boot(): void
    {
        // disable database translations
        config(['translation-loader.translation_loaders' => null]);
    }

    public function mount(): void
    {
        if (config('flux.install_done')) {
            abort(403);
        }

        $this->dbForm->host = config('database.connections.mysql.host');
        $this->dbForm->port = config('database.connections.mysql.port');
        $this->dbForm->database = config('database.connections.mysql.database');
        $this->dbForm->username = config('database.connections.mysql.username');
        $this->dbForm->password = config('database.connections.mysql.password');
    }

    #[Computed]
    public function title(): string
    {
        return $this->steps[$this->step]['title'] ?? __('Welcome to the Installation Process of Flux ERP');
    }

    #[Computed]
    public function steps(): array
    {
        return [
            [
                'view' => 'start',
            ],
            [
                'view' => 'language',
                'property' => 'languageForm',
                'title' => __('Language'),
                'callback' => function () {
                    $browserLanguage = Str::before(
                        explode(',', request()->server('HTTP_ACCEPT_LANGUAGE'))[1],
                        ';'
                    );

                    data_set($this->languageForm, 'language_code', $browserLanguage, false);
                    data_set($this->languageForm, 'iso_name', $browserLanguage, false);
                },
            ],
            [
                'view' => 'currency',
                'property' => 'currencyForm',
                'title' => __('Currency'),
            ],
            [
                'view' => 'client',
                'property' => 'clientForm',
                'title' => __('Client'),
            ],
            [
                'view' => 'vat-rates',
                'title' => __('Vat Rates'),
                'validation' => function () {
                    if (! $this->vatRates) {
                        throw ValidationException::withMessages([
                            'vatRates' => [__('At least one vat rate is required.')],
                        ]);
                    }
                },
            ],
            [
                'view' => 'payment-type',
                'property' => 'paymentTypeForm',
                'title' => __('Payment Type'),
                'rules' => function () {
                    $rules = CreatePaymentType::make([])->setRulesFromRulesets()->getRules();
                    $rules['clients'] = 'nullable';

                    return $rules;
                },
            ],
            [
                'view' => 'user',
                'property' => 'userForm',
                'title' => __('User'),
            ],
            [
                'view' => 'finish',
                'title' => __('Installation Complete!'),
            ],
        ];
    }

    public function rendering(): void
    {
        if ($this->languageForm->language_code) {
            app()->setLocale($this->languageForm->language_code);
        }
    }

    public function render(): View
    {
        return view('flux::livewire.install-wizard.install-wizard')->title('Install Wizard');
    }

    public function testDatabaseConnection(): void
    {
        $this->dbForm->validate();

        CommandJob::dispatchSync('flux:init-env', [
            'keyValues' => 'app_url:' . request()->getSchemeAndHttpHost() . ',' .
                'log_channel:single' . ',' .
                'app_debug:true' . ',' .
                'db_host:' . $this->dbForm->host . ',' .
                'db_port:' . $this->dbForm->port . ',' .
                'db_database:' . $this->dbForm->database . ',' .
                'db_username:' . $this->dbForm->username . ',' .
                'db_password:' . ($this->dbForm->password ?? null),
            '--use-default' => true,
        ]);

        CommandJob::dispatchSync('migrate', ['--force' => true]);

        $this->databaseConnectionSuccessful = true;

        $this->requestRefresh = true;
    }

    public function reload(): void
    {
        $this->requestRefresh = false;
    }

    #[Renderless]
    public function start(): void
    {
        $batch = Bus::batch([[
            new CommandJob('migrate', ['--force' => true]),
            new CommandJob('init:permissions'),
            new CommandJob('storage:link'),
            new CommandJob(VapidKeysGenerateCommand::class, ['--force' => true]),
            new CommandJob('cache:clear'),
            new CommandJob('route:clear'),
            new CommandJob('view:clear'),
            new CommandJob('config:clear'),
        ]])
            ->progress(function (Batch $batch) {
                InstallProcessOutputEvent::dispatch(
                    $batch->id,
                    $batch->progress()
                );
            })
            ->then(function (Batch $batch) {
                InstallProcessOutputEvent::dispatch(
                    $batch->id,
                    $batch->progress(),
                    'Finished',
                    ['Installation finished.']
                );
            })->catch(
                function (Batch $batch, Throwable $e) {
                    InstallProcessOutputEvent::dispatch(
                        $batch->id,
                        $batch->progress(),
                        'Error',
                        [$e->getMessage()]
                    );
                }
            )
            ->dispatch();

        $this->batchId = $batch->id;

        $this->dispatch('batch-id', $batch->id);
    }

    public function continue(): void
    {
        $step = $this->steps[$this->step] ?? false;
        $nextStep = $this->steps[$this->step + 1] ?? false;

        if (! $nextStep) {
            $this->skipRender();
            $this->finish();

            $this->redirect(route('login'));

            return;
        }

        $this->resetErrorBag();
        if ($step['property'] ?? false) {
            $rules = $step['rules'] ?? null;
            if (is_callable($rules)) {
                $rules = $rules();
            }

            $this->{$step['property']}->validateSave($rules);
        }

        if ($step['validation'] ?? false) {
            $step['validation']();
        }

        if ($nextStep['callback'] ?? false) {
            $nextStep['callback']();
        }

        $this->step++;

        $this->js(<<<'JS'
            $nextTick(() => {
                document.querySelector('[autofocus]')?.focus();
            });
        JS);
    }

    public function addVatRate(): void
    {
        $this->vatRateForm->rate_percentage = bcdiv($this->vatRateForm->rate_percentage_frontend, 100);
        $this->vatRateForm->validateSave();

        $this->vatRates[] = $this->vatRateForm->toArray();

        $this->vatRateForm->reset();
    }

    public function removeVatRate(int $index): void
    {
        unset($this->vatRates[$index]);
    }

    private function finish(): void
    {
        CommandJob::dispatchSync('flux:init-env', [
            'keyValues' => 'app_locale:' . $this->languageForm->language_code . ',' .
                'app_name:' . $this->clientForm->name . ',' .
                'flux_install_done:true',
        ]);

        $this->languageForm->iso_name = $this->languageForm->language_code;
        $this->languageForm->setCheckPermission(false)->save();

        $this->currencyForm->setCheckPermission(false)->save();
        $this->clientForm->setCheckPermission(false)->save();

        $this->userForm->language_id = $this->languageForm->id;
        $this->userForm->setCheckPermission(false)->save();

        resolve_static(User::class, 'query')
            ->whereKey($this->userForm->id)
            ->first()
            ->assignRole(
                resolve_static(Role::class, 'query')
                    ->where('name', 'admin')
                    ->where('guard_name', 'web')
                    ->first()
            );

        if ($this->languageForm->language_code !== 'en' && resolve_static(Language::class, 'query')->where('language_code', 'en')->doesntExist()) {
            $this->languageForm->reset();

            $this->languageForm->name = 'English';
            $this->languageForm->language_code = 'en';
            $this->languageForm->iso_name = 'en';
            $this->languageForm->setCheckPermission(false)->save();
        }

        foreach ($this->vatRates as $vatRate) {
            $this->vatRateForm->reset();
            $this->vatRateForm->fill($vatRate);
            $this->vatRateForm->setCheckPermission(false)->save();
        }

        $this->paymentTypeForm->clients = [$this->clientForm->id];
        $this->paymentTypeForm->setCheckPermission(false)->save();

        foreach (OrderTypeEnum::cases() as $orderType) {
            if (resolve_static(OrderType::class, 'query')->where('order_type_enum', $orderType)->doesntExist()) {
                CreateOrderType::make([
                    'client_id' => $this->clientForm->id,
                    'name' => __($orderType->name),
                    'order_type_enum' => $orderType,
                ])->execute();
            }
        }

        if (resolve_static(PriceList::class, 'query')->where('price_list_code', 'default')->doesntExist()) {
            CreatePriceList::make([
                'name' => __('Default'),
                'price_list_code' => 'default',
                'is_net' => true,
                'is_default' => true,
            ])->execute();
        }

        if (resolve_static(Warehouse::class, 'query')->where('name', 'Default')->doesntExist()) {
            CreateWarehouse::make([
                'name' => __('Default'),
                'is_default' => true,
            ])->execute();
        }
    }
}

<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Console\Commands\Init\InitPermissions;
use FluxErp\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Activitylog\Facades\CauserResolver;

class FluxSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LanguageTableSeeder::class);
        Artisan::call(InitPermissions::class);
        Role::findOrCreate('Super Admin');
        $this->call(UserTableSeeder::class);
        CauserResolver::resolveUsing(function () {
            return \FluxErp\Models\User::query()->inRandomOrder()->first();
        });

        $this->call(CurrencyTableSeeder::class);
        $this->call(CountryTableSeeder::class);
        $this->call(CountryRegionTableSeeder::class);
        $this->call(PriceListTableSeeder::class);
        $this->call(LeadLossReasonTableSeeder::class);
        $this->call(LeadStateTableSeeder::class);

        $this->call(ClientTableSeeder::class);
        $this->call(BankConnectionTableSeeder::class);
        $this->call(PaymentTypeTableSeeder::class);
        $this->call(OrderTypeTableSeeder::class);
        $this->call(RecordOriginTableSeeder::class);
        $this->call(IndustryTableSeeder::class);

        $this->call(ContactTableSeeder::class);
        $this->call(AddressTypeTableSeeder::class);
        $this->call(AddressTableSeeder::class);
        $this->call(ContactOptionTableSeeder::class);
        $this->call(CategoryTableSeeder::class);
        $this->call(ProjectTableSeeder::class);
        $this->call(TaskTableSeeder::class);
        $this->call(LeadTableSeeder::class);

        $this->call(WorkTimeTypeTableSeeder::class);
        $this->call(WorkTimeTableSeeder::class);

        $this->call(ContactBankConnectionTableSeeder::class);
        $this->call(SepaMandateTableSeeder::class);
        $this->call(ProductOptionGroupTableSeeder::class);
        $this->call(ProductOptionTableSeeder::class);
        $this->call(ProductPropertyTableSeeder::class);
        $this->call(UnitTableSeeder::class);
        $this->call(VatRateTableSeeder::class);
        $this->call(ProductTableSeeder::class);
        $this->call(ProductCrossSellingTableSeeder::class);
        $this->call(WarehouseTableSeeder::class);
        $this->call(StockPostingTableSeeder::class);
        $this->call(PriceTableSeeder::class);
        $this->call(OrderTableSeeder::class);
        $this->call(OrderPositionTableSeeder::class);
        $this->call(TransactionTableSeeder::class);
        $this->call(DiscountTableSeeder::class);
        $this->call(SerialNumberTableSeeder::class);
        $this->call(RoleTableSeeder::class);
        $this->call(CalendarTableSeeder::class);
        $this->call(CalendarEventTableSeeder::class);
        $this->call(TicketTypeTableSeeder::class);
        $this->call(TicketTableSeeder::class);
        $this->call(CommentTableSeeder::class);
    }
}

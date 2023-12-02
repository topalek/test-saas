<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Subscriptions\Enums\FeatureType;
use App\Modules\Subscriptions\Models\Feature;
use App\Modules\Subscriptions\Models\Plan;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'install';

    public function handle()
    {
        $this->info('Installation started');
        $this->install();
        $this->info('Installation completed');
    }

    private function install()
    {
        $user = User::query()
            ->firstOrCreate([
                'email' => 'admin@example.com',
            ], [
                'name'              => 'admin',
                'email_verified_at' => now(),
                'password'          => bcrypt('123123')
            ]);


        $trial = Plan::firstOrCreate([
            'name' => 'trial',
        ], [
            'description' => 'Trial desc',
            'price'       => 0,
            'currency'    => 'rub',
            'sort_order'  => 1,
            'duration'    => 1,
        ]);

        $bronze = Plan::firstOrCreate([
            'name' => 'bronze',
        ], [
            'description' => 'Bronze desc',
            'price'       => 100,
            'currency'    => 'rub',
            'sort_order'  => 1,
            'duration'    => 2,
        ]);

        $silver = Plan::firstOrCreate([
            'name' => 'silver',
        ], [
            'description' => 'Silver desc',
            'price'       => 300,
            'currency'    => 'rub',
            'sort_order'  => 1,
            'duration'    => 3,
        ]);

        $gold = Plan::firstOrCreate([
            'name' => 'gold',
        ], [
            'description' => 'Gold desc',
            'price'       => 500,
            'currency'    => 'rub',
            'sort_order'  => 1,
            'duration'    => 5,
        ]);

        Feature::query()->firstOrCreate([
            'code' => '123',
        ], [
            'plan_id'     => 123,
            'name'        => 123,
            'description' => 123,
            'type'        => FeatureType::limit,
            'limit'       => 1,
        ]);

        $user->subscribeTo($trial);
    }
}

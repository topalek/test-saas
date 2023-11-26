<?php

namespace App\Console\Commands;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Console\Command;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;

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
                    ])
        ;

        $limitedFeature = Feature::firstOrCreate([
            'name' => 'add-tasks-limited',
        ], [
            'title' => 'Количество задач',
            'consumable' => true,
        ]);

        $unlimitedFeature = Feature::firstOrCreate([
            'name' => 'add-tasks-unlimited',
        ], [
            'title' => 'Неограниченное количество задач',
            'consumable' => false,
        ]);


        $bronze = Plan::firstOrCreate([
            'name' => 'bronze',
        ], [
            'periodicity_type' => PeriodicityType::Day,
            'periodicity'      => 2,
            'price' => 100,
        ]);

        $silver = Plan::firstOrCreate([
            'name' => 'silver',
        ], [
            'periodicity_type' => PeriodicityType::Day,
            'periodicity'      => 3,
            'price' => 300,
        ]);

        $gold = Plan::firstOrCreate([
            'name' => 'gold',
        ], [
            'periodicity_type' => PeriodicityType::Day,
            'periodicity'      => 5,
            'price' => 500,
        ]);

        $unlim = Plan::firstOrCreate([
            'name' => 'unlim',
        ], [
            'periodicity_type' => PeriodicityType::Day,
            'periodicity'      => 5,
            'price'            => 1000,
        ]);

        $trialPlan = Plan::firstOrCreate([
            'name' => 'trial',
        ], [
            'periodicity_type' => PeriodicityType::Day,
            'periodicity'      => 1,
        ]);

        if ($bronze->features()->count() == 0) {
            $bronze->features()->attach($limitedFeature, ['charges' => 3]);
            $silver->features()->attach($limitedFeature, ['charges' => 5]);
            $gold->features()->attach($limitedFeature, ['charges' => 10]);

            $unlim->features()->attach($unlimitedFeature);

            $trialPlan->features()->attach($limitedFeature, ['charges' => 3]);

            $user->subscribeTo($trialPlan);
        }
    }
}

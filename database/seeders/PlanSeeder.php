<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Feature;
use LucasDotVin\Soulbscription\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $unlim = Plan::create([
            'name'             => 'unlim',
            'periodicity_type' => PeriodicityType::Day,
            'periodicity'      => 5,
        ]);
        $bronze = Plan::create([
            'name'             => 'bronze',
            'periodicity_type' => PeriodicityType::Day,
            'periodicity'      => 2,
        ]);

        $silver = Plan::create([
            'name'             => 'silver',
            'periodicity_type' => PeriodicityType::Day,
            'periodicity'      => 3,
        ]);

        $gold = Plan::create([
            'name'             => 'gold',
            'periodicity_type' => PeriodicityType::Day,
            'periodicity'      => 5,
        ]);

        $trialPlan = Plan::create([
            'name'             => 'trial',
            'periodicity_type' => PeriodicityType::Day,
            'periodicity'      => 1,
        ]);

        $limitedFeature = Feature::where('name', 'add-tasks-limited')->first();
        $unlimitedFeature = Feature::where('name', 'add-tasks-unlimited')->first();

        $bronze->features()->attach($limitedFeature, ['charges' => 3]);
        $silver->features()->attach($limitedFeature, ['charges' => 5]);
        $gold->features()->attach($limitedFeature, ['charges' => 10]);

        $unlim->features()->attach($unlimitedFeature);

        $trialPlan->features()->attach($limitedFeature, ['charges' => 3]);
    }
}

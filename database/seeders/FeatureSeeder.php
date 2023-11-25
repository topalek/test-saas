<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Models\Feature;

class FeatureSeeder extends Seeder
{
    public function run()
    {
        Feature::create([
            'consumable' => true,
            'name'       => 'add-tasks-limited',
        ]);

        Feature::create([
            'consumable' => false,
            'name'       => 'add-tasks-unlimited',
        ]);
    }
}

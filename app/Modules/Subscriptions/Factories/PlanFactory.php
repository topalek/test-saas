<?php

namespace App\Modules\Subscriptions\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanFactory extends Factory
{

    public function definition(): array
    {
        return [
            'name'        => 'Testing Plan ' . Str::random(7),
            'description' => 'This is a testing plan.',
            'price'       => (float)mt_rand(10, 200),
            'currency'    => 'EUR',
            'duration'    => 30,
        ];
    }
}

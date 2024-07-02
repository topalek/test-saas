<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Subscriptions\Enums\FeatureType;
use App\Modules\Subscriptions\Models\Feature;
use App\Modules\Subscriptions\Models\Plan;
use Illuminate\Console\Command;

use function Laravel\Prompts\error;

class InstallCommand extends Command
{
    protected $signature = 'install';

    public function handle()
    {
        $this->info('Installation started');
        $this->install();
        $this->info('Installation completed');
    }

    private function install(): void
    {
        $glava = 15;
        $part = 1;
        // "https://books3.audio-books.club/books/9665/%s_%02d.MP3"
        while ( $part <= 78) {
                $url = sprintf("https://books3.audio-books.club/books/14668/%02d.mp3",$part);
                \Laravel\Prompts\info($url);

                try {
                    $cont = file_get_contents($url);
                    $name = sprintf("%02d.MP3",$part);
                    file_put_contents($name, $cont);
                    $part++;
                } catch (\Exception $e){
                    error($e->getMessage());
                    $part++;
                }
        }
        dd('Done;');
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
            'currency_id' => 'rub',
            'sort'        => 1,
            'period'      => 1,
        ]);

        $bronze = Plan::firstOrCreate([
            'name' => 'bronze',
        ], [
            'description' => 'Bronze desc',
            'price'       => 100,
            'currency_id' => 'rub',
            'sort'        => 1,
            'period'      => 2,
        ]);

        $silver = Plan::firstOrCreate([
            'name' => 'silver',
        ], [
            'description' => 'Silver desc',
            'price'       => 300,
            'currency_id' => 'rub',
            'sort'        => 1,
            'period'      => 3,
        ]);

        $gold = Plan::firstOrCreate([
            'name' => 'gold',
        ], [
            'description' => 'Gold desc',
            'price'       => 500,
            'currency_id' => 'rub',
            'sort'        => 1,
            'period'      => 5,
        ]);

        $createTask = Feature::query()->firstOrCreate([
            'code' => 'create.task',
        ], [
            'name'        => 'Create task limit',
            'description' => 'Create task limit description',
            'type'        => FeatureType::limit,
        ]);

        $trial->features()->attach($createTask, ['value' => 2]);
        $bronze->features()->attach($createTask, ['value' => 5]);
        $silver->features()->attach($createTask, ['value' => 7]);
        $gold->features()->attach($createTask, ['value' => 10]);

        $user->subscribeTo($trial);
    }
}

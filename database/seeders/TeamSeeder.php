<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fixed_team_names = [
            'Chelsea',
            'Arsenal',
            'Liverpool',
            'Manchester City'
        ];

        foreach ($fixed_team_names as $fixed_team_name){
            Team::factory()->create([
                'name' => $fixed_team_name
            ]);
        }
    }
}

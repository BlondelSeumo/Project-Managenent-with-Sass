<?php

use Illuminate\Database\Seeder;
use App\Plan;
class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Plan::create([
            'name' => 'Free Plan',
            'price' => 0,
            'duration' => 'Unlimited',
            'max_workspaces' => 1,
            'max_users' => 5,
            'max_clients' => 5,
            'max_projects' => 5
        ]);
    }
}

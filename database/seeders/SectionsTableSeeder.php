<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        DB::table('sections')->insert([
            [
                'id' => 1,
                'title' => 'Packages',
                'subTitle' => 'Best packages as per your budget',
                'isActive' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}

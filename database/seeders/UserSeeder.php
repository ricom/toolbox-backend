<?php

namespace Database\Seeders;

use App\Models\Save;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(2)->has(Save::factory()->count(2),'saves')->create();
        User::factory()->count(2)->anonym()->has(Save::factory()->count(2),'saves')->create();
    }
}
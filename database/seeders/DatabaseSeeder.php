<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with real FEB UNIKU data.
     */
    public function run(): void
    {
        $this->call(PplRealDataSeeder::class);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\DevUsersSeeder;
use Database\Seeders\DevTicketsSeeder;
use Database\Seeders\DevKnowledgeBaseSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DevUsersSeeder::class,
            DevTicketsSeeder::class,
            DevKnowledgeBaseSeeder::class,
        ]);
    }
}

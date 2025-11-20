<?php

namespace Database\Seeders;

use App\Models\WordPressSite;
use Illuminate\Database\Seeder;

class WordPressSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WordPressSite::factory()
            ->count(5)
            ->state(fn (array $attributes) => [
                'server_host' => '192.0.2.'.rand(10, 50),
            ])
            ->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Utilisateur::factory()->create([
            'courriel' => 'test@example.com',
            'mot_de_passe' => 'test',
        ]);

        $this->call([
            RacesSeeder::class,
            ClassesSeeder::class,
            HistoriquesSeeder::class,
        ]);
    }
}

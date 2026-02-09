<?php

namespace Database\Seeders;

use App\Models\Mascota;
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

        Mascota::factory()->create([
            'nombre' => 'Solovino',
            'animal' => 'Perro',
            'edad' => 5,
            'descripcion' => 'Es un perro rescatado muy leal.',
            'raza' => 'Mestizo',
        ]);
    
    }
}

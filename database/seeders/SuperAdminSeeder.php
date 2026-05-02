<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Validamos que exista el email en el .env para no crear usuarios vacíos
        $email = env('SUPERADMIN_EMAIL');
        $password = env('SUPERADMIN_PASSWORD');

        if ($email && $password) {
            // updateOrCreate evita que se duplique el usuario si ejecutas el seeder dos veces
            User::updateOrCreate(
                ['email' => $email], // Condición de búsqueda
                [
                    'name' => 'Bryan (CEO)',
                    'password' => Hash::make($password),
                    'role' => 'super_admin',
                ]
            );
            
            $this->command->info('Super Admin creado correctamente.');
        } else {
            $this->command->error('Faltan las credenciales del Super Admin en el archivo .env');
        }
    }
}
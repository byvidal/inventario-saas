<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Tax;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear la Empresa (Tu cliente de prueba)
        $company = Company::create([
            'name' => 'Mi Primera Empresa SaaS',
            'tax_id' => '123456789',
            'is_active' => true,
        ]);

        // 2. Crear la Sucursal Principal
        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Sucursal Principal',
            'is_main' => true,
        ]);

        // 3. Crear el Usuario Administrador (Dueño) vinculado a la empresa
        User::create([
            'company_id' => $company->id,
            'name' => 'Admin Inventario',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        // 4. Run child seeders for better organization
        $this->call([
            CategorySeeder::class,
            UnitSeeder::class,
            TaxSeeder::class,
            BrandSeeder::class,
        ]);
    }
}
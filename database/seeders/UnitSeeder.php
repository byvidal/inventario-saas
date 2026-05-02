<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\Company;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company or create one
        $company = Company::first();
        if (!$company) {
            $company = Company::create([
                'name' => 'Default Company',
                'is_active' => true,
            ]);
        }

        // Standard units
        $units = [
            ['name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['name' => 'Gram', 'abbreviation' => 'g'],
            ['name' => 'Liter', 'abbreviation' => 'l'],
            ['name' => 'Milliliter', 'abbreviation' => 'ml'],
            ['name' => 'Meter', 'abbreviation' => 'm'],
            ['name' => 'Centimeter', 'abbreviation' => 'cm'],
            ['name' => 'Piece', 'abbreviation' => 'pcs'],
            ['name' => 'Box', 'abbreviation' => 'box'],
            ['name' => 'Pack', 'abbreviation' => 'pack'],
            ['name' => 'Dozen', 'abbreviation' => 'dz'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate([
                'company_id' => $company->id,
                'name' => $unit['name'],
                'abbreviation' => $unit['abbreviation'],
            ]);
        }
    }
}

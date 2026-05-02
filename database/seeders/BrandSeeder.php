<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
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

        // Sample brands
        $brands = [
            'Generic',
            'Premium',
            'Budget',
            'Luxury',
            'Standard',
        ];

        foreach ($brands as $name) {
            Brand::firstOrCreate([
                'company_id' => $company->id,
                'name' => $name,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Tax;
use App\Models\Company;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
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

        // Common tax rates
        $taxes = [
            ['name' => 'VAT 19%', 'rate' => 19.00],
            ['name' => 'VAT 5%', 'rate' => 5.00],
            ['name' => 'VAT 0%', 'rate' => 0.00],
            ['name' => 'Sales Tax 10%', 'rate' => 10.00],
        ];

        foreach ($taxes as $tax) {
            Tax::firstOrCreate([
                'company_id' => $company->id,
                'name' => $tax['name'],
                'rate' => $tax['rate'],
            ]);
        }
    }
}

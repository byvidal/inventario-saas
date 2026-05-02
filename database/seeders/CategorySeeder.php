<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
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

        // Sample categories
        $categories = [
            'Electronics',
            'Clothing',
            'Books',
            'Home & Garden',
            'Sports & Outdoors',
            'Beauty & Personal Care',
            'Office Supplies',
            'Toys & Games',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate([
                'company_id' => $company->id,
                'name' => $name,
            ]);
        }
    }
}

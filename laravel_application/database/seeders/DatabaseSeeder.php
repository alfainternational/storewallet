<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            SudaneseCitiesSeeder::class,
            CurrenciesSeeder::class,
            CategoriesSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('✓ 70+ Sudanese cities added');
        $this->command->info('✓ 10 currencies added');
        $this->command->info('✓ 10 main categories with 40+ subcategories added');
    }
}

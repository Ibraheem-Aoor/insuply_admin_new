<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use MercadoPago\Resources\Customer\PaymentMethod;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            // UserSeeder::class
            PaymentMethodSeeder::class,
        ]);
    }
}

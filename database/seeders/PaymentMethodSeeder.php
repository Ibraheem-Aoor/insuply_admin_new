<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BusinessSetting::updateOrCreate([
            'key' =>
                'moyassar'
        ], [
            'key' => 'stripe',
            'value' => json_encode([
                'status' => 1,
                'api_key' => config('services.moyassar.api_key'),
                'published_key' => config('services.moyassar.published_key'),
            ]),
            'updated_at' => now(),
        ]);

        Setting::updateOrCreate([
            'key_name' => 'moyassar',
        ], [
            'key_name' => 'moyassar',
            'live_values' => json_encode([
                'gateway' => 'moyassar',
                'mode' => 'live',
                'status' => 0,
                'api_key' => null,
                'published_key' => null,
            ]),
            'test_values' => json_encode([
                'gateway' => 'moyassar',
                'mode' => 'test',
                'status' => 1,
                'api_key' => config('services.moyassar.api_key'),
                'published_key' => config('services.moyassar.published_key'),
            ]),
            'settings_type' => 'payment_config',
            'mode' => 'test',
            "additional_data" => json_encode([
                'gateway_title' => 'Moyassar',
                'gateway_image' => null,
            ]),
        ]);
    }
}

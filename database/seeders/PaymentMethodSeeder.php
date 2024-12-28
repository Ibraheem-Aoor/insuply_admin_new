<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
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
                'api_key' => "sk_test_mTYLd1vfG68L8YTzGArfrgAVvFocHfndhZmYmaNR",
                'published_key' => "pk_test_RQgfLV2Uqpt2kwCPt61E2howpN6ya56bhua9x45U",
            ]),
            'updated_at' => now(),
        ]);
    }
}

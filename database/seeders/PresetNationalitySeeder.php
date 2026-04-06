<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nationality;

class PresetNationalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nationalities = [
            // Southeast Asian Countries
            [
                'name' => 'Malaysian',
                'symbol' => 'MY',
                'status' => 10,
            ],
            [
                'name' => 'Indonesian',
                'symbol' => 'ID',
                'status' => 10,
            ],
            [
                'name' => 'Singaporean',
                'symbol' => 'SG',
                'status' => 10,
            ],
            [
                'name' => 'Thai',
                'symbol' => 'TH',
                'status' => 10,
            ],
            [
                'name' => 'Vietnamese',
                'symbol' => 'VN',
                'status' => 10,
            ],
            [
                'name' => 'Filipino',
                'symbol' => 'PH',
                'status' => 10,
            ],
            [
                'name' => 'Bruneian',
                'symbol' => 'BN',
                'status' => 10,
            ],
            [
                'name' => 'Cambodian',
                'symbol' => 'KH',
                'status' => 10,
            ],
            [
                'name' => 'Laotian',
                'symbol' => 'LA',
                'status' => 10,
            ],
            [
                'name' => 'Myanmar',
                'symbol' => 'MM',
                'status' => 10,
            ],
            [
                'name' => 'Timorese',
                'symbol' => 'TL',
                'status' => 10,
            ],

            // East Asian Countries
            [
                'name' => 'Chinese',
                'symbol' => 'CN',
                'status' => 10,
            ],
            [
                'name' => 'Japanese',
                'symbol' => 'JP',
                'status' => 10,
            ],
            [
                'name' => 'South Korean',
                'symbol' => 'KR',
                'status' => 10,
            ],
            [
                'name' => 'North Korean',
                'symbol' => 'KP',
                'status' => 10,
            ],
            [
                'name' => 'Taiwanese',
                'symbol' => 'TW',
                'status' => 10,
            ],
            [
                'name' => 'Hong Kong',
                'symbol' => 'HK',
                'status' => 10,
            ],
            [
                'name' => 'Macanese',
                'symbol' => 'MO',
                'status' => 10,
            ],

            // South Asian Countries
            [
                'name' => 'Indian',
                'symbol' => 'IN',
                'status' => 10,
            ],
            [
                'name' => 'Pakistani',
                'symbol' => 'PK',
                'status' => 10,
            ],
            [
                'name' => 'Bangladeshi',
                'symbol' => 'BD',
                'status' => 10,
            ],
            [
                'name' => 'Sri Lankan',
                'symbol' => 'LK',
                'status' => 10,
            ],
            [
                'name' => 'Nepalese',
                'symbol' => 'NP',
                'status' => 10,
            ],
            [
                'name' => 'Bhutanese',
                'symbol' => 'BT',
                'status' => 10,
            ],
            [
                'name' => 'Maldivian',
                'symbol' => 'MV',
                'status' => 10,
            ],
            [
                'name' => 'Afghan',
                'symbol' => 'AF',
                'status' => 10,
            ],

            // Central Asian Countries
            [
                'name' => 'Kazakh',
                'symbol' => 'KZ',
                'status' => 10,
            ],
            [
                'name' => 'Uzbek',
                'symbol' => 'UZ',
                'status' => 10,
            ],
            [
                'name' => 'Kyrgyz',
                'symbol' => 'KG',
                'status' => 10,
            ],
            [
                'name' => 'Tajik',
                'symbol' => 'TJ',
                'status' => 10,
            ],
            [
                'name' => 'Turkmen',
                'symbol' => 'TM',
                'status' => 10,
            ],

            // Western Asian Countries (Middle East)
            [
                'name' => 'Turkish',
                'symbol' => 'TR',
                'status' => 10,
            ],
            [
                'name' => 'Iranian',
                'symbol' => 'IR',
                'status' => 10,
            ],
            [
                'name' => 'Iraqi',
                'symbol' => 'IQ',
                'status' => 10,
            ],
            [
                'name' => 'Syrian',
                'symbol' => 'SY',
                'status' => 10,
            ],
            [
                'name' => 'Lebanese',
                'symbol' => 'LB',
                'status' => 10,
            ],
            [
                'name' => 'Jordanian',
                'symbol' => 'JO',
                'status' => 10,
            ],
            [
                'name' => 'Israeli',
                'symbol' => 'IL',
                'status' => 10,
            ],
            [
                'name' => 'Palestinian',
                'symbol' => 'PS',
                'status' => 10,
            ],
            [
                'name' => 'Saudi Arabian',
                'symbol' => 'SA',
                'status' => 10,
            ],
            [
                'name' => 'Emirati',
                'symbol' => 'AE',
                'status' => 10,
            ],
            [
                'name' => 'Kuwaiti',
                'symbol' => 'KW',
                'status' => 10,
            ],
            [
                'name' => 'Qatari',
                'symbol' => 'QA',
                'status' => 10,
            ],
            [
                'name' => 'Bahraini',
                'symbol' => 'BH',
                'status' => 10,
            ],
            [
                'name' => 'Omani',
                'symbol' => 'OM',
                'status' => 10,
            ],
            [
                'name' => 'Yemeni',
                'symbol' => 'YE',
                'status' => 10,
            ],
        ];

        foreach ($nationalities as $nationality) {
            Nationality::updateOrCreate(
                ['symbol' => $nationality['symbol']],
                $nationality
            );
        }
    }
}

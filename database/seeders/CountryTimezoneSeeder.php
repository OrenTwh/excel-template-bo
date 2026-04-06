<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryTimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $timezones = [
            // Afghanistan
            'AF' => 'Asia/Kabul',
            // Aland Islands
            'AX' => 'Europe/Helsinki',
            // Albania
            'AL' => 'Europe/Tirane',
            // Algeria
            'DZ' => 'Africa/Algiers',
            // American Samoa
            'AS' => 'Pacific/Pago_Pago',
            // Andorra
            'AD' => 'Europe/Andorra',
            // Angola
            'AO' => 'Africa/Luanda',
            // Anguilla
            'AI' => 'America/Anguilla',
            // Antarctica (using multiple, but Palmer as default)
            'AQ' => 'Antarctica/Palmer',
            // Antigua and Barbuda
            'AG' => 'America/Antigua',
            // Argentina
            'AR' => 'America/Argentina/Buenos_Aires',
            // Armenia
            'AM' => 'Asia/Yerevan',
            // Aruba
            'AW' => 'America/Aruba',
            // Australia (using Sydney as default)
            'AU' => 'Australia/Sydney',
            // Austria
            'AT' => 'Europe/Vienna',
            // Azerbaijan
            'AZ' => 'Asia/Baku',
            // Bahamas
            'BS' => 'America/Nassau',
            // Bahrain
            'BH' => 'Asia/Bahrain',
            // Bangladesh
            'BD' => 'Asia/Dhaka',
            // Barbados
            'BB' => 'America/Barbados',
            // Belarus
            'BY' => 'Europe/Minsk',
            // Belgium
            'BE' => 'Europe/Brussels',
            // Belize
            'BZ' => 'America/Belize',
            // Benin
            'BJ' => 'Africa/Porto-Novo',
            // Bermuda
            'BM' => 'Atlantic/Bermuda',
            // Bhutan
            'BT' => 'Asia/Thimphu',
            // Bolivia
            'BO' => 'America/La_Paz',
            // Bonaire, Sint Eustatius and Saba
            'BQ' => 'America/Kralendijk',
            // Bosnia and Herzegovina
            'BA' => 'Europe/Sarajevo',
            // Botswana
            'BW' => 'Africa/Gaborone',
            // Bouvet Island
            'BV' => 'Europe/Oslo',
            // Brazil (using Sao Paulo as default)
            'BR' => 'America/Sao_Paulo',
            // British Indian Ocean Territory
            'IO' => 'Indian/Chagos',
            // Brunei
            'BN' => 'Asia/Brunei',
            // Bulgaria
            'BG' => 'Europe/Sofia',
            // Burkina Faso
            'BF' => 'Africa/Ouagadougou',
            // Burundi
            'BI' => 'Africa/Bujumbura',
            // Cambodia
            'KH' => 'Asia/Phnom_Penh',
            // Cameroon
            'CM' => 'Africa/Douala',
            // Canada (using Toronto as default)
            'CA' => 'America/Toronto',
            // Cape Verde
            'CV' => 'Atlantic/Cape_Verde',
            // Cayman Islands
            'KY' => 'America/Cayman',
            // Central African Republic
            'CF' => 'Africa/Bangui',
            // Chad
            'TD' => 'Africa/Ndjamena',
            // Chile
            'CL' => 'America/Santiago',
            // China
            'CN' => 'Asia/Shanghai',
            // Christmas Island
            'CX' => 'Indian/Christmas',
            // Cocos (Keeling) Islands
            'CC' => 'Indian/Cocos',
            // Colombia
            'CO' => 'America/Bogota',
            // Comoros
            'KM' => 'Indian/Comoro',
            // Congo
            'CG' => 'Africa/Brazzaville',
            // Congo, Democratic Republic
            'CD' => 'Africa/Kinshasa',
            // Cook Islands
            'CK' => 'Pacific/Rarotonga',
            // Costa Rica
            'CR' => 'America/Costa_Rica',
            // Cote D'Ivoire
            'CI' => 'Africa/Abidjan',
            // Croatia
            'HR' => 'Europe/Zagreb',
            // Cuba
            'CU' => 'America/Havana',
            // Curacao
            'CW' => 'America/Curacao',
            // Cyprus
            'CY' => 'Asia/Nicosia',
            // Czech Republic
            'CZ' => 'Europe/Prague',
            // Denmark
            'DK' => 'Europe/Copenhagen',
            // Djibouti
            'DJ' => 'Africa/Djibouti',
            // Dominica
            'DM' => 'America/Dominica',
            // Dominican Republic
            'DO' => 'America/Santo_Domingo',
            // Ecuador
            'EC' => 'America/Guayaquil',
            // Egypt
            'EG' => 'Africa/Cairo',
            // El Salvador
            'SV' => 'America/El_Salvador',
            // Equatorial Guinea
            'GQ' => 'Africa/Malabo',
            // Eritrea
            'ER' => 'Africa/Asmara',
            // Estonia
            'EE' => 'Europe/Tallinn',
            // Ethiopia
            'ET' => 'Africa/Addis_Ababa',
            // Falkland Islands
            'FK' => 'Atlantic/Stanley',
            // Faroe Islands
            'FO' => 'Atlantic/Faroe',
            // Fiji
            'FJ' => 'Pacific/Fiji',
            // Finland
            'FI' => 'Europe/Helsinki',
            // France
            'FR' => 'Europe/Paris',
            // French Guiana
            'GF' => 'America/Cayenne',
            // French Polynesia
            'PF' => 'Pacific/Tahiti',
            // French Southern Territories
            'TF' => 'Indian/Kerguelen',
            // Gabon
            'GA' => 'Africa/Libreville',
            // Gambia
            'GM' => 'Africa/Banjul',
            // Georgia
            'GE' => 'Asia/Tbilisi',
            // Germany
            'DE' => 'Europe/Berlin',
            // Ghana
            'GH' => 'Africa/Accra',
            // Gibraltar
            'GI' => 'Europe/Gibraltar',
            // Greece
            'GR' => 'Europe/Athens',
            // Greenland
            'GL' => 'America/Godthab',
            // Grenada
            'GD' => 'America/Grenada',
            // Guadeloupe
            'GP' => 'America/Guadeloupe',
            // Guam
            'GU' => 'Pacific/Guam',
            // Guatemala
            'GT' => 'America/Guatemala',
            // Guernsey
            'GG' => 'Europe/Guernsey',
            // Guinea
            'GN' => 'Africa/Conakry',
            // Guinea-Bissau
            'GW' => 'Africa/Bissau',
            // Guyana
            'GY' => 'America/Guyana',
            // Haiti
            'HT' => 'America/Port-au-Prince',
            // Heard Island and McDonald Islands
            'HM' => 'Indian/Kerguelen',
            // Vatican City
            'VA' => 'Europe/Vatican',
            // Honduras
            'HN' => 'America/Tegucigalpa',
            // Hong Kong
            'HK' => 'Asia/Hong_Kong',
            // Hungary
            'HU' => 'Europe/Budapest',
            // Iceland
            'IS' => 'Atlantic/Reykjavik',
            // India
            'IN' => 'Asia/Kolkata',
            // Indonesia (using Jakarta as default)
            'ID' => 'Asia/Jakarta',
            // Iran
            'IR' => 'Asia/Tehran',
            // Iraq
            'IQ' => 'Asia/Baghdad',
            // Ireland
            'IE' => 'Europe/Dublin',
            // Isle of Man
            'IM' => 'Europe/Isle_of_Man',
            // Israel
            'IL' => 'Asia/Jerusalem',
            // Italy
            'IT' => 'Europe/Rome',
            // Jamaica
            'JM' => 'America/Jamaica',
            // Japan
            'JP' => 'Asia/Tokyo',
            // Jersey
            'JE' => 'Europe/Jersey',
            // Jordan
            'JO' => 'Asia/Amman',
            // Kazakhstan (using Almaty as default)
            'KZ' => 'Asia/Almaty',
            // Kenya
            'KE' => 'Africa/Nairobi',
            // Kiribati
            'KI' => 'Pacific/Tarawa',
            // North Korea
            'KP' => 'Asia/Pyongyang',
            // South Korea
            'KR' => 'Asia/Seoul',
            // Kosovo
            'XK' => 'Europe/Belgrade',
            // Kuwait
            'KW' => 'Asia/Kuwait',
            // Kyrgyzstan
            'KG' => 'Asia/Bishkek',
            // Laos
            'LA' => 'Asia/Vientiane',
            // Latvia
            'LV' => 'Europe/Riga',
            // Lebanon
            'LB' => 'Asia/Beirut',
            // Lesotho
            'LS' => 'Africa/Maseru',
            // Liberia
            'LR' => 'Africa/Monrovia',
            // Libya
            'LY' => 'Africa/Tripoli',
            // Liechtenstein
            'LI' => 'Europe/Vaduz',
            // Lithuania
            'LT' => 'Europe/Vilnius',
            // Luxembourg
            'LU' => 'Europe/Luxembourg',
            // Macao
            'MO' => 'Asia/Macau',
            // Macedonia
            'MK' => 'Europe/Skopje',
            // Madagascar
            'MG' => 'Indian/Antananarivo',
            // Malawi
            'MW' => 'Africa/Blantyre',
            // Malaysia
            'MY' => 'Asia/Kuala_Lumpur',
            // Maldives
            'MV' => 'Indian/Maldives',
            // Mali
            'ML' => 'Africa/Bamako',
            // Malta
            'MT' => 'Europe/Malta',
            // Marshall Islands
            'MH' => 'Pacific/Majuro',
            // Martinique
            'MQ' => 'America/Martinique',
            // Mauritania
            'MR' => 'Africa/Nouakchott',
            // Mauritius
            'MU' => 'Indian/Mauritius',
            // Mayotte
            'YT' => 'Indian/Mayotte',
            // Mexico (using Mexico City as default)
            'MX' => 'America/Mexico_City',
            // Micronesia
            'FM' => 'Pacific/Chuuk',
            // Moldova
            'MD' => 'Europe/Chisinau',
            // Monaco
            'MC' => 'Europe/Monaco',
            // Mongolia
            'MN' => 'Asia/Ulaanbaatar',
            // Montenegro
            'ME' => 'Europe/Podgorica',
            // Montserrat
            'MS' => 'America/Montserrat',
            // Morocco
            'MA' => 'Africa/Casablanca',
            // Mozambique
            'MZ' => 'Africa/Maputo',
            // Myanmar
            'MM' => 'Asia/Rangoon',
            // Namibia
            'NA' => 'Africa/Windhoek',
            // Nauru
            'NR' => 'Pacific/Nauru',
            // Nepal
            'NP' => 'Asia/Kathmandu',
            // Netherlands
            'NL' => 'Europe/Amsterdam',
            // Netherlands Antilles
            'AN' => 'America/Curacao',
            // New Caledonia
            'NC' => 'Pacific/Noumea',
            // New Zealand
            'NZ' => 'Pacific/Auckland',
            // Nicaragua
            'NI' => 'America/Managua',
            // Niger
            'NE' => 'Africa/Niamey',
            // Nigeria
            'NG' => 'Africa/Lagos',
            // Niue
            'NU' => 'Pacific/Niue',
            // Norfolk Island
            'NF' => 'Pacific/Norfolk',
            // Northern Mariana Islands
            'MP' => 'Pacific/Saipan',
            // Norway
            'NO' => 'Europe/Oslo',
            // Oman
            'OM' => 'Asia/Muscat',
            // Pakistan
            'PK' => 'Asia/Karachi',
            // Palau
            'PW' => 'Pacific/Palau',
            // Palestine
            'PS' => 'Asia/Gaza',
            // Panama
            'PA' => 'America/Panama',
            // Papua New Guinea
            'PG' => 'Pacific/Port_Moresby',
            // Paraguay
            'PY' => 'America/Asuncion',
            // Peru
            'PE' => 'America/Lima',
            // Philippines
            'PH' => 'Asia/Manila',
            // Pitcairn
            'PN' => 'Pacific/Pitcairn',
            // Poland
            'PL' => 'Europe/Warsaw',
            // Portugal
            'PT' => 'Europe/Lisbon',
            // Puerto Rico
            'PR' => 'America/Puerto_Rico',
            // Qatar
            'QA' => 'Asia/Qatar',
            // Réunion
            'RE' => 'Indian/Reunion',
            // Romania
            'RO' => 'Europe/Bucharest',
            // Russia (using Moscow as default)
            'RU' => 'Europe/Moscow',
            // Rwanda
            'RW' => 'Africa/Kigali',
            // Saint Barthelemy
            'BL' => 'America/St_Barthelemy',
            // Saint Helena
            'SH' => 'Atlantic/St_Helena',
            // Saint Kitts and Nevis
            'KN' => 'America/St_Kitts',
            // Saint Lucia
            'LC' => 'America/St_Lucia',
            // Saint Martin
            'MF' => 'America/Marigot',
            // Saint Pierre and Miquelon
            'PM' => 'America/Miquelon',
            // Saint Vincent and the Grenadines
            'VC' => 'America/St_Vincent',
            // Samoa
            'WS' => 'Pacific/Apia',
            // San Marino
            'SM' => 'Europe/San_Marino',
            // Sao Tome and Principe
            'ST' => 'Africa/Sao_Tome',
            // Saudi Arabia
            'SA' => 'Asia/Riyadh',
            // Senegal
            'SN' => 'Africa/Dakar',
            // Serbia
            'RS' => 'Europe/Belgrade',
            // Serbia and Montenegro
            'CS' => 'Europe/Belgrade',
            // Seychelles
            'SC' => 'Indian/Mahe',
            // Sierra Leone
            'SL' => 'Africa/Freetown',
            // Singapore
            'SG' => 'Asia/Singapore',
            // Sint Maarten
            'SX' => 'America/Lower_Princes',
            // Slovakia
            'SK' => 'Europe/Bratislava',
            // Slovenia
            'SI' => 'Europe/Ljubljana',
            // Solomon Islands
            'SB' => 'Pacific/Guadalcanal',
            // Somalia
            'SO' => 'Africa/Mogadishu',
            // South Africa
            'ZA' => 'Africa/Johannesburg',
            // South Georgia and South Sandwich Islands
            'GS' => 'Atlantic/South_Georgia',
            // South Sudan
            'SS' => 'Africa/Juba',
            // Spain
            'ES' => 'Europe/Madrid',
            // Sri Lanka
            'LK' => 'Asia/Colombo',
            // Sudan
            'SD' => 'Africa/Khartoum',
            // Suriname
            'SR' => 'America/Paramaribo',
            // Svalbard and Jan Mayen
            'SJ' => 'Arctic/Longyearbyen',
            // Swaziland
            'SZ' => 'Africa/Mbabane',
            // Sweden
            'SE' => 'Europe/Stockholm',
            // Switzerland
            'CH' => 'Europe/Zurich',
            // Syria
            'SY' => 'Asia/Damascus',
            // Taiwan
            'TW' => 'Asia/Taipei',
            // Tajikistan
            'TJ' => 'Asia/Dushanbe',
            // Tanzania
            'TZ' => 'Africa/Dar_es_Salaam',
            // Thailand
            'TH' => 'Asia/Bangkok',
            // Timor-Leste
            'TL' => 'Asia/Dili',
            // Togo
            'TG' => 'Africa/Lome',
            // Tokelau
            'TK' => 'Pacific/Fakaofo',
            // Tonga
            'TO' => 'Pacific/Tongatapu',
            // Trinidad and Tobago
            'TT' => 'America/Port_of_Spain',
            // Tunisia
            'TN' => 'Africa/Tunis',
            // Turkey
            'TR' => 'Europe/Istanbul',
            // Turkmenistan
            'TM' => 'Asia/Ashgabat',
            // Turks and Caicos Islands
            'TC' => 'America/Grand_Turk',
            // Tuvalu
            'TV' => 'Pacific/Funafuti',
            // Uganda
            'UG' => 'Africa/Kampala',
            // Ukraine
            'UA' => 'Europe/Kiev',
            // United Arab Emirates
            'AE' => 'Asia/Dubai',
            // United Kingdom
            'GB' => 'Europe/London',
            // United States (using New York as default)
            'US' => 'America/New_York',
            // US Minor Outlying Islands
            'UM' => 'Pacific/Wake',
            // Uruguay
            'UY' => 'America/Montevideo',
            // Uzbekistan
            'UZ' => 'Asia/Tashkent',
            // Vanuatu
            'VU' => 'Pacific/Efate',
            // Venezuela
            'VE' => 'America/Caracas',
            // Vietnam
            'VN' => 'Asia/Ho_Chi_Minh',
            // British Virgin Islands
            'VG' => 'America/Tortola',
            // US Virgin Islands
            'VI' => 'America/St_Thomas',
            // Wallis and Futuna
            'WF' => 'Pacific/Wallis',
            // Western Sahara
            'EH' => 'Africa/El_Aaiun',
            // Yemen
            'YE' => 'Asia/Aden',
            // Zambia
            'ZM' => 'Africa/Lusaka',
            // Zimbabwe
            'ZW' => 'Africa/Harare',
        ];

        // Update each country with its timezone
        foreach ($timezones as $isoCode => $timezone) {
            DB::table('countries')
                ->where('iso_alpha2_code', $isoCode)
                ->update([
                    'timezone' => $timezone,
                    'updated_at' => now()
                ]);
        }

        return "Timezone data has been successfully added to all countries.\n";
    }
}
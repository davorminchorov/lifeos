<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Country
    |--------------------------------------------------------------------------
    |
    | The default country code used throughout the application.
    | This will be the primary country for all modules.
    | Uses ISO 3166-1 alpha-2 country codes.
    |
    */
    'default' => env('DEFAULT_COUNTRY', 'MK'),

    /*
    |--------------------------------------------------------------------------
    | Supported Countries
    |--------------------------------------------------------------------------
    |
    | List of supported countries with their holidays.
    | The key should be the 2-letter ISO 3166-1 alpha-2 country code.
    | Holidays use MM-DD format (month-day) and will be applied to current year.
    |
    */
    'supported' => [
        'MK' => [
            'name' => 'North Macedonia',
            'holidays' => [
                ['date' => '01-01', 'name' => 'New Year\'s Day', 'description' => 'First day of the year'],
                ['date' => '01-07', 'name' => 'Orthodox Christmas', 'description' => 'Orthodox Christian Christmas'],
                ['date' => '05-01', 'name' => 'Labour Day', 'description' => 'International Workers\' Day'],
                ['date' => '05-24', 'name' => 'Saints Cyril and Methodius Day', 'description' => 'Day of Slavonic Educators'],
                ['date' => '08-02', 'name' => 'Republic Day', 'description' => 'Ilinden - Republic Day'],
                ['date' => '09-08', 'name' => 'Independence Day', 'description' => 'Independence from Yugoslavia'],
                ['date' => '10-11', 'name' => 'Day of Macedonian Uprising', 'description' => 'Day of the Macedonian Revolutionary Struggle'],
                ['date' => '10-23', 'name' => 'Day of the Macedonian Revolutionary Struggle', 'description' => 'Celebration of the start of the Macedonian resistance movement'],
                ['date' => '12-08', 'name' => 'Saint Clement of Ohrid Day', 'description' => 'Day of Saint Clement of Ohrid'],
            ],
        ],
        'US' => [
            'name' => 'United States',
            'holidays' => [
                ['date' => '01-01', 'name' => 'New Year\'s Day', 'description' => 'First day of the year'],
                ['date' => '07-04', 'name' => 'Independence Day', 'description' => 'US Independence Day'],
                ['date' => '11-11', 'name' => 'Veterans Day', 'description' => 'Honoring military veterans'],
                ['date' => '12-25', 'name' => 'Christmas Day', 'description' => 'Christian celebration of the birth of Jesus Christ'],
            ],
        ],
        'GB' => [
            'name' => 'United Kingdom',
            'holidays' => [
                ['date' => '01-01', 'name' => 'New Year\'s Day', 'description' => 'First day of the year'],
                ['date' => '12-25', 'name' => 'Christmas Day', 'description' => 'Christian celebration of the birth of Jesus Christ'],
                ['date' => '12-26', 'name' => 'Boxing Day', 'description' => 'Day after Christmas'],
            ],
        ],
        'DE' => [
            'name' => 'Germany',
            'holidays' => [
                ['date' => '01-01', 'name' => 'New Year\'s Day', 'description' => 'First day of the year'],
                ['date' => '05-01', 'name' => 'Labour Day', 'description' => 'International Workers\' Day'],
                ['date' => '10-03', 'name' => 'German Unity Day', 'description' => 'Day of German reunification'],
                ['date' => '12-25', 'name' => 'Christmas Day', 'description' => 'Christian celebration of the birth of Jesus Christ'],
                ['date' => '12-26', 'name' => 'Boxing Day', 'description' => 'Second day of Christmas'],
            ],
        ],
        'FR' => [
            'name' => 'France',
            'holidays' => [
                ['date' => '01-01', 'name' => 'New Year\'s Day', 'description' => 'First day of the year'],
                ['date' => '05-01', 'name' => 'Labour Day', 'description' => 'International Workers\' Day'],
                ['date' => '05-08', 'name' => 'Victory in Europe Day', 'description' => 'End of World War II in Europe'],
                ['date' => '07-14', 'name' => 'Bastille Day', 'description' => 'French National Day'],
                ['date' => '11-11', 'name' => 'Armistice Day', 'description' => 'End of World War I'],
                ['date' => '12-25', 'name' => 'Christmas Day', 'description' => 'Christian celebration of the birth of Jesus Christ'],
            ],
        ],
        'CA' => [
            'name' => 'Canada',
            'holidays' => [
                ['date' => '01-01', 'name' => 'New Year\'s Day', 'description' => 'First day of the year'],
                ['date' => '07-01', 'name' => 'Canada Day', 'description' => 'Canadian National Day'],
                ['date' => '12-25', 'name' => 'Christmas Day', 'description' => 'Christian celebration of the birth of Jesus Christ'],
                ['date' => '12-26', 'name' => 'Boxing Day', 'description' => 'Day after Christmas'],
            ],
        ],
        'AU' => [
            'name' => 'Australia',
            'holidays' => [
                ['date' => '01-01', 'name' => 'New Year\'s Day', 'description' => 'First day of the year'],
                ['date' => '01-26', 'name' => 'Australia Day', 'description' => 'National day of Australia'],
                ['date' => '04-25', 'name' => 'ANZAC Day', 'description' => 'Remembrance day for Australian and New Zealand Army Corps'],
                ['date' => '12-25', 'name' => 'Christmas Day', 'description' => 'Christian celebration of the birth of Jesus Christ'],
                ['date' => '12-26', 'name' => 'Boxing Day', 'description' => 'Day after Christmas'],
            ],
        ],
        'RS' => [
            'name' => 'Serbia',
            'holidays' => [
                ['date' => '01-01', 'name' => 'New Year\'s Day', 'description' => 'First day of the year'],
                ['date' => '01-07', 'name' => 'Orthodox Christmas', 'description' => 'Orthodox Christian Christmas'],
                ['date' => '02-15', 'name' => 'Statehood Day', 'description' => 'Serbian Statehood Day'],
                ['date' => '05-01', 'name' => 'Labour Day', 'description' => 'International Workers\' Day'],
                ['date' => '11-11', 'name' => 'Armistice Day', 'description' => 'End of World War I'],
            ],
        ],
        'BG' => [
            'name' => 'Bulgaria',
            'holidays' => [
                ['date' => '01-01', 'name' => 'New Year\'s Day', 'description' => 'First day of the year'],
                ['date' => '03-03', 'name' => 'Liberation Day', 'description' => 'National Day of Bulgaria'],
                ['date' => '05-01', 'name' => 'Labour Day', 'description' => 'International Workers\' Day'],
                ['date' => '05-06', 'name' => 'St. George\'s Day', 'description' => 'Day of the Bulgarian Army'],
                ['date' => '05-24', 'name' => 'Bulgarian Education and Culture Day', 'description' => 'Day of Slavonic Alphabet, Bulgarian Education and Culture'],
                ['date' => '09-06', 'name' => 'Unification Day', 'description' => 'Unification of Bulgaria'],
                ['date' => '09-22', 'name' => 'Independence Day', 'description' => 'Bulgarian Independence Day'],
            ],
        ],
    ],
];

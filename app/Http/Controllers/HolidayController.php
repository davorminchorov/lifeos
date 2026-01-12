<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * Display a listing of Macedonian holidays.
     */
    public function index()
    {
        // National Macedonian Holidays
        $nationalHolidays = [
            [
                'name' => 'New Year\'s Day',
                'name_mk' => 'Нова Година',
                'date' => 'January 1',
                'type' => 'national',
                'description' => 'Celebration of the new year'
            ],
            [
                'name' => 'New Year\'s Day',
                'name_mk' => 'Нова Година',
                'date' => 'January 2',
                'type' => 'national',
                'description' => 'Second day of New Year celebration'
            ],
            [
                'name' => 'Christmas Eve',
                'name_mk' => 'Бадник (Православен)',
                'date' => 'January 6',
                'type' => 'orthodox',
                'description' => 'Orthodox Christmas Eve'
            ],
            [
                'name' => 'Christmas Day',
                'name_mk' => 'Божиќ (Православен)',
                'date' => 'January 7',
                'type' => 'orthodox',
                'description' => 'Orthodox Christmas Day'
            ],
            [
                'name' => 'Easter',
                'name_mk' => 'Велигден (Православен)',
                'date' => 'Variable (April/May)',
                'type' => 'orthodox',
                'description' => 'Orthodox Easter - celebrated on different dates each year'
            ],
            [
                'name' => 'Labour Day',
                'name_mk' => 'Ден на трудот',
                'date' => 'May 1',
                'type' => 'national',
                'description' => 'International Workers\' Day'
            ],
            [
                'name' => 'Saints Cyril and Methodius Day',
                'name_mk' => 'Св. Кирил и Методиј',
                'date' => 'May 24',
                'type' => 'national',
                'description' => 'Day of the Slavonic Educators and Culture'
            ],
            [
                'name' => 'Ilinden (St. Elijah\'s Day)',
                'name_mk' => 'Илинден',
                'date' => 'August 2',
                'type' => 'national',
                'description' => 'Uprising Day - National holiday commemorating the Ilinden Uprising of 1903'
            ],
            [
                'name' => 'Republic Day',
                'name_mk' => 'Ден на Републиката',
                'date' => 'August 2',
                'type' => 'national',
                'description' => 'Day of the Republic of North Macedonia'
            ],
            [
                'name' => 'Independence Day',
                'name_mk' => 'Ден на независноста',
                'date' => 'September 8',
                'type' => 'national',
                'description' => 'Independence from Yugoslavia (1991)'
            ],
            [
                'name' => 'Day of the Macedonian Revolutionary Struggle',
                'name_mk' => 'Ден на македонската револуционерна борба',
                'date' => 'October 23',
                'type' => 'national',
                'description' => 'Commemorates the founding of VMRO in 1893'
            ],
            [
                'name' => 'Saint Clement of Ohrid Day',
                'name_mk' => 'Св. Климент Охридски',
                'date' => 'December 8',
                'type' => 'national',
                'description' => 'Day of Saint Clement of Ohrid - patron saint of North Macedonia'
            ],
        ];

        return view('holidays.index', compact('nationalHolidays'));
    }
}

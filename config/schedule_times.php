<?php

return [
    /*
    |--------------------------------------------------------------------------
    | School Day Period Times
    |--------------------------------------------------------------------------
    |
    | Class schedules store a period number. These times provide the canonical
    | start and end time for each of the seven daily periods, including the two
    | breaks in between.
    |
    */

    'periods' => [
        1 => ['start' => '08:00', 'end' => '08:45'],
        2 => ['start' => '08:45', 'end' => '09:30'],
        3 => ['start' => '09:45', 'end' => '10:30'],
        4 => ['start' => '10:30', 'end' => '11:15'],
        5 => ['start' => '11:35', 'end' => '12:20'],
        6 => ['start' => '12:20', 'end' => '13:05'],
        7 => ['start' => '13:05', 'end' => '13:50'],
    ],

    'breaks' => [
        ['name' => 'first_break', 'after_period' => 2, 'start' => '09:30', 'end' => '09:45'],
        ['name' => 'second_break', 'after_period' => 4, 'start' => '11:15', 'end' => '11:35'],
    ],
];

<?php

return [
    'ssn' => '###-##-####',

    // Phone
    'phone' => [
        7 => '###-####',
        10 => '(###) ###-####',
        11 => '# (###) ###-####'
    ],

    // Datetime
    'date' => '%m/%d/%Y',
    'time' => '%I:%M%p',
    'datetime' => '%m/%d/%Y %I:%M%p',

    // Numbers
    'number' => [
        'thousands' => ',',
        'decimals' => '.',
        'places' => 2
    ],

    // Currency
    'currency' => [
        'code' => 'USD #',
        'dollar' => '$#',
        'cents' => '#&cent;',
        'negative' => '(#)',
        'use' => 'dollar'
    ],

    // Localization
    'pluralForms' => 2,
    'pluralRule' => function ($n) {
        return $n != 1 ? 1 : 0;
    }
];
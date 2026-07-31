<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CCR Hourly Site Codes
    |--------------------------------------------------------------------------
    |
    | Sites enabled for the CCR Hourly module (Entry + Dashboard).
    |
    */

    'ccr_site_codes' => ['021C', '025C', '017C', '022C'],

    /*
    |--------------------------------------------------------------------------
    | CCR Site Materials
    |--------------------------------------------------------------------------
    |
    | Material types available per CCR site for dashboard/entry UI.
    |
    */

    'ccr_site_materials' => [
        '021C' => ['limestone', 'shalestone'],
        '025C' => ['limestone'],
        '017C' => ['ob', 'coal'],
        '022C' => ['ob', 'coal', 'top_soil'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Coal Density Factor (BCM → Mton)
    |--------------------------------------------------------------------------
    */

    'coal_density_factor' => [
        'default' => 1.0,
        '022C' => 1.0,
        '017C' => 1.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Production Source Mode per Site
    |--------------------------------------------------------------------------
    |
    | parallel: trip data for analysis; OB/Coal manual in daily entry
    | trip_derived: production_records auto-populated from trip rollup
    |
    */

    'production_source' => [
        '022C' => env('PRODUCTION_SOURCE_022C', 'parallel'),
    ],

];

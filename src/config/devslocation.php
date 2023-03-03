<?php

return [
    
    /**
     * LOCATION SEARCH DRIVER
     * 
     * Allowed values: database, algolia, typesense, meilesearch
     * Currently supported: database, algolia
     * Default Driver: database
     */
    'driver' => env('DEVSLOCATION_DRIVER','algolia'),

    'prefix' => env('DEVSLOCATION_PREFIX', 'v1/devslocation/api/'),

    'algolia' => [
        'app_key' => env('DEVSLOCATION_ALGOLIA_APP', ''),
        'secret_key' => env('DEVSLOCATION_ALGOLIA_SECRET', ''),
        'index_name' => env('DEVSLOCATION_ALGOLIA_INDEX', '')
    ],
    
    'weight' => [
        'name','region','city','state'
    ],
    'local_government_area' => env('DEVSLOCATION_LOCAL_GOV_AREA',true)

   
];

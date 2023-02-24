<?php
use Devsfort\Location\Http\Controllers\LocationController;

/*
* This is the main app route [Devsfort Location Operator]
*/
Route::prefix('api/v1/devsfort')->group(function () {
    Route::get('/', [LocationController::class,'index']);
    Route::post('/get-locations', [LocationController::class,'getLocations']);
});
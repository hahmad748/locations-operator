<?php

namespace Devsfort\Location\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Devsfort\Location\Managers\SearchManager;
use Illuminate\Support\Str;


class LocationController extends Controller
{
  
    /**
     * Returning the view of the app with the required data.
     *
     * @param int $id
     * @return void
     */
    public function index($id = null)
    {
        $response = [
            'status' => true,
            'message' => 'Welcome to Devsfort Locations Manager'
        ];
         // send the response
        return Response::json($response, 200);
    }

    public function getLocations(Request $request)
    {
        return SearchManager::getResults($request);    
    }

}

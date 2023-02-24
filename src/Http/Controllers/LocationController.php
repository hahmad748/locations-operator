<?php

namespace Devsfort\Location\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Devsfort\Location\Models\Location;


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
        try{
            $limit = 10;
            if((int) $request->limit > 0){
                $limit = (int) $request->limit;
            }
            $response = array();
            $searchLevel = 'name';
            if(isset($request->search_key)){
                $searchKey = strtolower(trim($request->search_key));
                $tokens = preg_split('/\s+/', $searchKey, -1, PREG_SPLIT_NO_EMPTY);
                // Name/ suburb level search
                $locations = Location::where('name', 'like', '%'.$searchKey.'%')->get();
                if($locations->count() <= 0){
                    $searchLevel = 'region';
                    // Search on region level
                    $locations = Location::where('region', 'like', '%'.$searchKey.'%')->get();
                    if($locations->count() <= 0){
                        $searchLevel = 'city';
                        // Search on city level
                        $locations = Location::where('city', 'like', '%'.$searchKey.'%')->get();
                        if($locations->count() <= 0){
                            $searchLevel = 'state';
                            // Search on state level
                            $locations = Location::where('state', 'like', '%'.$searchKey.'%')->orWhere('state_code', 'like', '%'.$searchKey.'%')->get();
                        }
                        if($locations->count() <= 0 && isset($request->include_local) && (bool) $request->include_local == true){
                            // Search on local government area
                            $searchLevel = 'local_government_area';
                            $locations = Location::where('local_government_area', 'like', '%'.$searchKey.'%')->get();
                        }
                    }
                }

                if($locations->count() <= 0){
                    $response = [
                        'status' => false,
                        'message' => 'No Results found with specified criteria',
                        'data' => []
                    ];
                    return Response::json($response, 400);
                }

                
                $locations_response = array_map(function($location) use($searchLevel){
                        $resp = [];

                        $city = !empty($location['city']) ? $location['city'] :'';
                        $state = !empty($location['state']) ? $location['state'] :'';
                        $region = !empty($location['region']) ? $location['region'] :'';
                        $name = !empty($location['name']) ? $location['name'] :'';
                        switch($searchLevel){
                            case 'name':   
                                $result = [];
                                $name    != '' ? $result[] =  $name : null; 
                                $region    != '' ? $result[] =  $region : null; 
                                $city    != '' ? $result[] =  $city : null; 
                                $state    != '' ? $result[] =  $state : null; 
                                $result[] =  $location['postcode'] ;
                                $result[] =  $location['country'] ;
                                 
                                 $resp = array(
                                    'result'    => implode(', ',$result) ,
                                    'suburb'    => $name,
                                    'state'     => $state,
                                    'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                                    'city'      => $city,
                                    'region'    => $region,
                                    'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                                    'country' => !empty($location['country']) ? $location['country'] :'',
                                    'postcode' => !empty($location['postcode']) ? $location['postcode'] :'',
                                    'type' => !empty($location['type']) ? $location['type'] :'',
                                    'search_level' => $searchLevel
                                );
                                 break;
                            case 'region':  
                                $result = [];
                                $region    != '' ? $result[] =  $region : null; 
                                $city    != '' ? $result[] =  $city : null; 
                                $state    != '' ? $result[] =  $state : null; 
                                $result[] =  $location['postcode'] ;
                                $result[] =  $location['country'] ;
                                 
                                 $resp = array(
                                    'result'    => implode(', ',$result) ,         
                                    'suburb'    => '',
                                    'state'     => $state,
                                    'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                                    'city'      => $city,
                                    'region'    => $region,
                                    'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                                    'country' => !empty($location['country']) ? $location['country'] :'',
                                    'postcode' => !empty($location['postcode']) ? $location['postcode'] :'',
                                    'type' => !empty($location['type']) ? $location['type'] :'',
                                    'search_level' => $searchLevel
                                );
                                break;
                            case 'city':   
                                $result = [];
                                $city    != '' ? $result[] =  $city : null; 
                                $state    != '' ? $result[] =  $state : null; 
                                $result[] =  $location['postcode'] ;
                                $result[] =  $location['country'] ;

                                 $resp = array(
                                    'result'    => implode(', ',$result) ,
                                    'suburb'    => '',
                                    'state'     => $state,
                                    'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                                    'city'      => $city,
                                    'region'    => '',
                                    'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                                    'country' => !empty($location['country']) ? $location['country'] :'',
                                    'postcode' => !empty($location['postcode']) ? $location['postcode'] :'',
                                    'type' => !empty($location['type']) ? $location['type'] :'',
                                    'search_level' => $searchLevel
                                );
                                break;
                            case 'state':    
                                $result = [];
                                $state    != '' ? $result[] =  $state : null; 
                                $result[] =  $location['postcode'] ;
                                $result[] =  $location['country'] ;

                                 $resp = array(
                                    'result'    => implode(', ',$result) ,
                                    'suburb'    => '',
                                    'state'     => $state,
                                    'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                                    'city'      => '',
                                    'region'    => '',
                                    'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                                    'country' => !empty($location['country']) ? $location['country'] :'',
                                    'postcode' => !empty($location['postcode']) ? $location['postcode'] :'',
                                    'type' => !empty($location['type']) ? $location['type'] :'',
                                    'search_level' => $searchLevel
                                );
                                break;
                            case 'local_government_area':
                                $result = [];
                                !empty($location['local_government_area']) ? $result[] =  $location['local_government_area'] : null; 
                                $state    != '' ? $result[] =  $state : null; 
                                $result[] =  $location['postcode'] ;
                                $result[] =  $location['country'] ;

                                 $resp = array(
                                    'result'    => implode(', ',$result) ,
                                    'suburb'    => '',
                                    'state'     => $state,
                                    'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                                    'city'      => '',
                                    'region'    => '',
                                    'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                                    'country' => !empty($location['country']) ? $location['country'] :'',
                                    'postcode' => !empty($location['postcode']) ? $location['postcode'] :'',
                                    'type' => !empty($location['type']) ? $location['type'] :'',
                                    'search_level' => $searchLevel
                                );
                                break;
                            default:   
                               $result = [];
                                $name    != '' ? $result[] =  $name : null; 
                                $region    != '' ? $result[] =  $region : null; 
                                $city    != '' ? $result[] =  $city : null; 
                                $state    != '' ? $result[] =  $state : null; 
                                $result[] =  $location['postcode'] ;
                                $result[] =  $location['country'] ;
                                
                                $resp = array(
                                    'result'    => implode(', ',$result) ,
                                    'suburb'    => $name,
                                    'state'     => $state,
                                    'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                                    'city'      => $city,
                                    'region'    => $region,
                                    'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                                    'country' => !empty($location['country']) ? $location['country'] :'',
                                    'postcode' => !empty($location['postcode']) ? $location['postcode'] :'',
                                    'type' => !empty($location['type']) ? $location['type'] :'',
                                    'search_level' => $searchLevel
                                );
                                 
                                 break;
                        }
                        return $resp;
                },$locations->toArray());


                $uniqueResults = array_reduce($locations_response, function($accumulator, $item) {
                    $result = $item['result'];
                    if (!in_array($result, array_column($accumulator, 'result'))) {
                        $accumulator[] = $item;
                    }
                    return $accumulator;
                },[]);

                $uniqueLimitedResponse = array_slice($uniqueResults, 0, $limit);
                if(sizeof($uniqueLimitedResponse) <= 0){
                    $response = [
                        'status' => false,
                        'message' => 'No Results found with specified criteria',
                        'data' => []
                    ];
                    return Response::json($response, 400);
                }

                $response = [
                    'status'  => true,
                    'message' => 'Location fetched successfully',
                    'data'    => $uniqueLimitedResponse
                ];
                
                return Response::json($response, 200);
            }else{
                $response = [
                    'status' => false,
                    'message' => 'Please provide search key. Missing attribute: search_key'
                ];
                return Response::json($response, 400);
            }
            
        }catch(\Exception $ex){
            $response = [
                'status' => false,
                'message' => $ex->getMessage()
            ];
            return Response::json($response, 400);
        }
        
    }

    public function apiResponse($location)
    {
        return $location->name;
    }

}

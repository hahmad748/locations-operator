<?php
namespace Devsfort\Location\Managers;

use Illuminate\Support\Facades\Response;
use Devsfort\Location\Managers\AlgoliaSearchManager;

class BaseManager 
{
    public static function errorResponse($message = "Something Went Wrong")
    {
        $response = [
            'status' => false,
            'message' => $message
        ];
        return Response::json($response, 400);
    }

    public static function successResponse($message,$data)
    {
        $response = [
            'status'  => true,
            'message' => $message || 'Success',
            'data'    => $data
        ];
        
        return Response::json($response, 200);
    }


    public static function getAlgoliaResultOnFilters($attribute,$query,$limit = 10){
        $manager    = AlgoliaSearchManager::getInstance()
                        ->query($query)
                        ->filter($attribute,$query)
                        ->limit($limit);
        $results    = $manager->searchResults();

        if($results && $results['num_of_results'] > 0){
            return  $results['results']; 
        }
        return false;
    }

    public static function getAlgoliaResultOnQuery($query,$limit = 10){
        $manager    = AlgoliaSearchManager::getInstance()
                        ->query($query)
                        ->unsetFilters()
                        ->limit($limit);
        $results    = $manager->searchResults();

        if($results && $results['num_of_results'] > 0){
            return  $results['results']; 
        }
        return false;
    }


    public static function locationTransformer($searchLevel,$locations){
        $locations_response = array_map(function($location) use($searchLevel){
                $resp = [];
                $city = !empty($location['city']) || !empty($location['urban_area']) ? $location['urban_area'] :'';
                $state = !empty($location['state']) ? $location['state'] :'';
                $region = !empty($location['region']) ? $location['region'] :'';
                $name = !empty($location['name']) ? $location['name'] :'';
                $country = !empty($location['country']) ? $location['country'] :'Australia';
                switch($searchLevel){
                    case 'name':   
                        $result = [];
                        $name    != '' ? $result[] =  $name : null; 
                        $region    != '' ? $result[] =  $region : null; 
                        $city    != '' ? $result[] =  $city : null; 
                        $state    != '' ? $result[] =  $state : null; 
                        // $result[] =  $location['postcode'] ;
                        $result[] = $country ;
                        
                        $resp = array(
                            'result'    => implode(', ',$result) ,
                            'suburb'    => $name,
                            'state'     => $state,
                            'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                            'city'      => $city,
                            'region'    => $region,
                            'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                            'country' => $country,
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
                        // $result[] =  $location['postcode'] ;
                        $result[] = $country ;
                        
                        $resp = array(
                            'result'    => implode(', ',$result) ,         
                            'suburb'    => '',
                            'state'     => $state,
                            'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                            'city'      => $city,
                            'region'    => $region,
                            'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                            'country' => $country,
                            'postcode' => !empty($location['postcode']) ? $location['postcode'] :'',
                            'type' => !empty($location['type']) ? $location['type'] :'',
                            'search_level' => $searchLevel
                        );
                        break;
                    case 'city':   
                        $result = [];
                        $city    != '' ? $result[] =  $city : null; 
                        $state    != '' ? $result[] =  $state : null; 
                        // $result[] =  $location['postcode'] ;
                        $result[] = $country ;

                        $resp = array(
                            'result'    => implode(', ',$result) ,
                            'suburb'    => '',
                            'state'     => $state,
                            'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                            'city'      => $city,
                            'region'    => '',
                            'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                            'country' => $country,
                            'postcode' => !empty($location['postcode']) ? $location['postcode'] :'',
                            'type' => !empty($location['type']) ? $location['type'] :'',
                            'search_level' => $searchLevel
                        );
                        break;
                    case 'urban_area':   
                        $result = [];
                        $city    != '' ? $result[] =  $city : null; 
                        $state    != '' ? $result[] =  $state : null; 
                        // $result[] =  $location['postcode'] ;
                        $result[] = $country ;

                        $resp = array(
                            'result'    => implode(', ',$result) ,
                            'suburb'    => '',
                            'state'     => $state,
                            'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                            'city'      => $city,
                            'region'    => '',
                            'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                            'country' => $country,
                            'postcode' => !empty($location['postcode']) ? $location['postcode'] :'',
                            'type' => !empty($location['type']) ? $location['type'] :'',
                            'search_level' => $searchLevel
                        );
                        break;
                    case 'state':    
                        $result = [];
                        $state    != '' ? $result[] =  $state : null; 
                        // $result[] =  $location['postcode'] ;
                        $result[] = $country ;

                        $resp = array(
                            'result'    => implode(', ',$result) ,
                            'suburb'    => '',
                            'state'     => $state,
                            'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                            'city'      => '',
                            'region'    => '',
                            'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                            'country' => $country,
                            'postcode' => !empty($location['postcode']) ? $location['postcode'] :'',
                            'type' => !empty($location['type']) ? $location['type'] :'',
                            'search_level' => $searchLevel
                        );
                        break;
                    case 'state_code':    
                        $result = [];
                        $state    != '' ? $result[] =  $state : null; 
                        // $result[] =  $location['postcode'] ;
                        $result[] = $country ;

                        $resp = array(
                            'result'    => implode(', ',$result) ,
                            'suburb'    => '',
                            'state'     => $state,
                            'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                            'city'      => '',
                            'region'    => '',
                            'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                            'country' => $country,
                            'postcode' => !empty($location['postcode']) ? $location['postcode'] :'',
                            'type' => !empty($location['type']) ? $location['type'] :'',
                            'search_level' => $searchLevel
                        );
                        break;
                    case 'local_government_area':
                        $result = [];
                        !empty($location['local_government_area']) ? $result[] =  $location['local_government_area'] : null; 
                        $state    != '' ? $result[] =  $state : null; 
                        // $result[] =  $location['postcode'] ;
                        $result[] = $country ;

                        $resp = array(
                            'result'    => implode(', ',$result) ,
                            'suburb'    => '',
                            'state'     => $state,
                            'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                            'city'      => '',
                            'region'    => '',
                            'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                            'country' => $country,
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
                        // $result[] =  $location['postcode'] ;
                        $result[] = $country ;
                        
                        $resp = array(
                            'result'    => implode(', ',$result) ,
                            'suburb'    => $name,
                            'state'     => $state,
                            'state_code' => !empty($location['state_code']) ? $location['state_code'] :'',
                            'city'      => $city,
                            'region'    => $region,
                            'local_government_area' => !empty($location['local_government_area']) ? $location['local_government_area'] :'',
                            'country' => $country,
                            'postcode' => !empty($location['postcode']) ? $location['postcode'] :'',
                            'type' => !empty($location['type']) ? $location['type'] :'',
                            'search_level' => $searchLevel
                        );
                        
                        break;
                }
                return $resp;
         },$locations);

         $uniqueResults = array_reduce($locations_response, function($accumulator, $item) {
            $result = $item['result'];
            if (!in_array($result, array_column($accumulator, 'result'))) {
                $accumulator[] = $item;
            }
            return $accumulator;
        },[]);

        return $uniqueResults;
    }

}

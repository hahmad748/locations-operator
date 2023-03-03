<?php
namespace Devsfort\Location\Managers;
use Devsfort\Location\Managers\BaseManager;
use Devsfort\Location\Models\Location;
use Illuminate\Http\Request;

class SearchManager extends BaseManager {
    

    public static function getResults(Request $request)
    {
        switch(config('devslocation.driver')){
            case 'database': 
                return self::getResultsFromDatabase($request);    
            break;
            case 'algolia': 
                return self::getResultsFromAlgolia($request);    
            break;
            default: 
                return self::errorResponse('Invalid Search Driver, Please configure the Search Driver');
        }
    }

    public static function getResultsFromDatabase(Request $request)
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
                    return BaseManager::errorResponse('No Results found with specified criteria');
                }

                $uniqueResults = BaseManager::locationTransformer($searchLevel,$locations->toArray());
                
                $uniqueLimitedResponse = array_slice($uniqueResults, 0, $limit);
                if(sizeof($uniqueLimitedResponse) <= 0){
                    return BaseManager::errorResponse('No Results found with specified criteria');
                }
                return BaseManager::successResponse('Location fetched successfully',$uniqueLimitedResponse);
            }else{
                return BaseManager::errorResponse('Please provide search key. Missing attribute: search_key');
            }
            
        }catch(\Exception $ex){
            return BaseManager::errorResponse($ex->getMessage());
        }
    }

    public static function getResultsFromAlgolia(Request $request)
    {
        $query = $request->search_key;
        $limit = (int) $request->limit > 0 ? (int) $request->limit : 10; 

        // Name/ suburb level search
        $response   =  BaseManager::getAlgoliaResult('name',$query,$limit); 
        
        if($response){
            $uniqueResults = BaseManager::locationTransformer('name',$response);
            return BaseManager::successResponse('Locations Fetched Successfully',$uniqueResults);
        }

        // Search on region level
        $response   =  BaseManager::getAlgoliaResult('region',$query,$limit); 
        if($response){
            $uniqueResults = BaseManager::locationTransformer('region',$response);
            return BaseManager::successResponse('Locations Fetched Successfully',$uniqueResults);
        }

          // Search on city / urban_area level
          $response   =  BaseManager::getAlgoliaResult('urban_area',$query,$limit); 
          if($response){
              $uniqueResults = BaseManager::locationTransformer('urban_area',$response);
              return BaseManager::successResponse('Locations Fetched Successfully',$uniqueResults);
          }
        // Search on state level
        $response   =  BaseManager::getAlgoliaResult('state',$query,$limit); 
        if($response){
            $uniqueResults = BaseManager::locationTransformer('state',$response);
            return BaseManager::successResponse('Locations Fetched Successfully',$uniqueResults);
        }
         // Search on state level
         $response   =  BaseManager::getAlgoliaResult('state_code',$query,$limit); 
         if($response){
             $uniqueResults = BaseManager::locationTransformer('state_code',$response);
             return BaseManager::successResponse('Locations Fetched Successfully',$uniqueResults);
         }

            
        
        return BaseManager::errorResponse('No Results found with specified criteria');

        
    }

}
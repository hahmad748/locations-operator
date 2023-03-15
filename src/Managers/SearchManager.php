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

    public static function depth_picker($arr, $temp_string, &$collect, $desired_length) {
        if ($temp_string != "" && count(explode(' ', $temp_string)) == $desired_length) {
            $collect []= $temp_string;
            return;
        }
    
        for ($i=0, $iMax = sizeof($arr); $i < $iMax; $i++) {
            $arrcopy = $arr;
            $elem = array_splice($arrcopy, $i, 1); // removes and returns the i'th element
            if (count(explode(' ', $temp_string)) < $desired_length) {
                self::depth_picker($arrcopy, $temp_string ." " . $elem[0], $collect, $desired_length);
            }
        }
    }
    public static function generateSqlCondition($column, $array) {
        $conditions = array();
        foreach ($array as $value) {
            $like_query = preg_replace('/\s+/', '%', trim($value));
            $like_query = "%$like_query%";
            $conditions[] = "$column like '$like_query'";
        }
        return implode(' OR ', $conditions);
    }
    



    public static function getResultsFromDatabase(Request $request)
    {
        try{
            $response = array();
            $finalLocations = array();
            $searchLevel = 'name';
            if(isset($request->search_key)){
                $searchKey = strtolower(trim($request->search_key));
                $tokens = preg_split('/\s+/', $searchKey, -1, PREG_SPLIT_NO_EMPTY);
                $collect = array();
                self::depth_picker($tokens, "", $collect,sizeof($tokens) + 1);
                $searchLevels = ['name','region','urban_area','state','state_code'];
                if($request->include_local){
                    $searchLevels[] = 'local_government_area';
                }
                $all_results = array();
                $results = array();
                foreach($searchLevels as $level){

                    $sql_condition = self::generateSqlCondition($level,$collect);
                    $locations = Location::whereRaw($sql_condition)->get();    
                    if ($locations->count() > 0) {
                        $values = $locations->toArray();
                        $existing_ids = array_unique(array_column($all_results, 'id'));
                        $new_values = array_filter($values, function($value) use ($existing_ids) {
                            return !in_array($value['id'], $existing_ids);
                        });
                        $uniqueResults = BaseManager::locationTransformer($level,$new_values);
                        $all_results = array_merge($all_results, $uniqueResults);
                        $results[] = $uniqueResults;
                    }
                }


                // // Name/ suburb level search
                // $sql_condition = self::generateSqlCondition($searchLevel,$collect);
                // $locations = Location::whereRaw($sql_condition)->get();
                // if($locations->count() <= 0){
                //     $searchLevel = 'region';
                //     // Search on region level
                //     $sql_condition = self::generateSqlCondition($searchLevel,$collect);
                //     $locations = Location::whereRaw($sql_condition)->get();
                //     if($locations->count() <= 0){
                //         $searchLevel = 'city';
                //         // Search on city level
                //         $sql_condition = self::generateSqlCondition($searchLevel,$collect);
                //         $locations = Location::whereRaw($sql_condition)->get();
                //         if($locations->count() <= 0){
                //             $searchLevel = 'state';
                //             // Search on state level
                //             $sql_condition = self::generateSqlCondition($searchLevel,$collect);
                //             $locations = Location::whereRaw($sql_condition)->get();
                //             if($locations->count() <= 0){
                //                 $searchLevel = 'state';
                //                 // Search on state level
                //                 $sql_condition = self::generateSqlCondition('state_code',$collect);
                //                 $locations = Location::whereRaw($sql_condition)->get();
                //             }
                //         }
                //         if($locations->count() <= 0 && isset($request->include_local) && (bool) $request->include_local == true){
                //             // Search on local government area
                //             $searchLevel = 'local_government_area';
                //             $sql_condition = self::generateSqlCondition('state_code',$collect);
                //             $locations = Location::whereRaw($sql_condition)->get();
                //         }
                //     }
                // }
                if(sizeof($all_results) <= 0){
                    return BaseManager::errorResponse('No Results found with specified criteria');
                }
                
                
                if(sizeof($all_results) <= 0){
                    return BaseManager::errorResponse('No Results found with specified criteria');
                }
                return BaseManager::successResponse('Location fetched successfully',$all_results);
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
        // $limit = (int) $request->limit > 0 ? (int) $request->limit : 10; 

        $searchLevel = ['name','region','urban_area','state','state_code'];

        foreach($searchLevel as $level){
            $response   =  BaseManager::getAlgoliaResultOnFilters($level,$query); 
            if($response){
                $uniqueResults = BaseManager::locationTransformer($level,$response);
                return BaseManager::successResponse('Locations Fetched Successfully',$uniqueResults);
            }    
        }

        //  Fallback method to fuzzy search
         $response   =  BaseManager::getAlgoliaResultOnQuery($query); 
         if($response){
            $uniqueResults = BaseManager::locationTransformer('name',$response);
            return BaseManager::successResponse('Locations Fetched Successfully',$uniqueResults);
        }

        
        return BaseManager::errorResponse('No Results found with specified criteria');

        
    }

}
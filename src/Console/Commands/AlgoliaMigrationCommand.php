<?php

namespace Devsfort\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\SearchClient;



class AlgoliaMigrationCommand extends Command
{

    protected $index_name      = "locations_index";
    protected $client          = NULL;
    protected $indexSettings   = [
        'searchableAttributes' => [
            'name',
            'state,state_code',
            'unordered(type)',
            'unordered(local_government_area)',
            'region'
        ],
        'attributesForFaceting' => [
            'searchable(region)',
            'searchable(time_zone)',
            'searchable(local_government_area)',
            'searchable(state)',
            'searchable(type)',
            'searchable(urban_area)'
        ],
        'sortFacetValuesBy' => 'count',
        'ranking' => [
            'typo',
            'geo',
            'words',
            'filters',
            'proximity',
            'attribute',
            'exact'
        ],
        'customRanking' => [
        ]
    ];
    public function __construct() {
        $config            = SearchConfig::create(config('devslocation.algolia.app_key'), config('devslocation.algolia.secret_key'));
        $this->index_name = config('devslocation.algolia.index_name');
        $this->client      = SearchClient::createWithConfig($config);
        $this->index       = $this->client->initIndex($this->index_name);
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devslocation:algolia-migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Algolia migrations for Locations';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->actionFullReindex();
    }

    public function actionFullReindex($limit = 500, $sleep = 3) {
        $this->info("Clearing Objects from Algolia");
        $index = $this->client->initIndex($this->index_name);
        $index->clearObjects();
        $this->info("Data Cleared Successfully");
        $this->fillData(-1, $limit, 'fullReindex', $sleep);
    }


    public function saveIndexing($saveObjects) {
        if (!isset($saveObjects) || count($saveObjects) < 1) {
            return false;
        }
        $index     = $this->client->initIndex($this->index_name);
        //$index->clearObjects();
        $index->saveObjects($saveObjects);
    }
    public function deleteByObjIds($objIds) {
        if (!isset($objIds) || count($objIds) < 1) {
            return false;
        }
        $index = $this->client->initIndex($this->index_name);
        $index->deleteObjects($objIds);
    }
    public function fillData($mtime, $limit, $actionType = null, $sleep = 3) {
        $condition = "";
        $done      = false;
        $last_id = - 1;

        while (!$done) {
            $sql             = "SELECT id AS objectID, au.* FROM locations au WHERE id > $last_id $condition LIMIT $limit";
            $res             = DB::connection('mysql')->select(DB::raw($sql));
            $this->line($sql);
            if(sizeof($res)) {
                $saveObjects  = [];
                $removeObjIds = [];
                foreach ($res as $object) {
                    $obj = $this->prepareObjects($object);
                    if (isset($obj) && $obj != null) {
                        $saveObjects[] =  $obj;
                    }
                    $last_id = $object->id;
                }
                if(count($saveObjects) > 0){
                    $this->saveIndexing($saveObjects);
                    $this->line("Importing ".count($saveObjects)." Items to Algolia ( ID > {$last_id} )");
                }
                if ($actionType == "update" && count($removeObjIds) > 0) {
                    $this->deleteByObjIds($removeObjIds);
                    $this->line("Removing ".count($removeObjIds)." Items from Algolia.");
                }
                $done = (sizeof($res) < $limit);
                sleep($sleep);
            }
        }

    }
    public function prepareObjects($object) {
        
        $latlon = [
            'lat' => ($object->latitude) ? $object->latitude : -33.8689365242,
            'lon' => ($object->longitude) ? $object->longitude :  151.205332512,
        ];

        $returnObject                             = [];
        $returnObject['id']                       = $object->id;
        $returnObject['objectID']                 = $object->id;
        $returnObject['name']                     = isset($object->name) ? $object->name :'';
        $returnObject['urban_area']               = isset($object->urban_area) ? strip_tags($object->urban_area) :'';
        $returnObject['state']                    = isset($object->state) ? strip_tags($object->state) :'';
        $returnObject['state_code']               = isset($object->state_code) ? $object->state_code :'';
        $returnObject['lat_lng']                  = $latlon;
        $returnObject['latitude']                 = isset($object->latitude) ? $object->latitude :-33.8689365242;
        $returnObject['longitude']                = isset($object->longitude) ?   (int) $object->longitude :151.205332512;
        $returnObject['elevation']                = isset($object->elevation) ?   (int) $object->elevation :0;
        $returnObject['population']               = isset($object->population) ? (int) $object->population :0;
        $returnObject['median_income']            = isset($object->median_income) ? (int) $object->median_income :0;
        $returnObject['type']                     = isset($object->type) ? (int) $object->type :'';
        $returnObject['postcode']                 = isset($object->postcode) ? (int)$object->postcode :0;
        $returnObject['area_sq_km']               = isset($object->area_sq_km) && !empty($object->area_sq_km) ? $object->area_sq_km :0;
        $returnObject['local_government_area']    = isset($object->local_government_area) && !empty($object->local_government_area) ? $object->local_government_area :'';
        $returnObject['region']                   = isset($object->region) && !empty($object->region) ? $object->region :'';
        $returnObject['time_zone']                = isset($object->time_zone) && !empty($object->time_zone) ? $object->time_zone :'';
        $returnObject['region_name']              = isset($object->region) && isset($object->name)?  $object->region.' | '.$object->name :'';
        
        return $returnObject;
    }
}

<?php

namespace Devsfort\Location\Managers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\SearchClient;
use Devsfort\Location\Patterns\Singleton;

class AlgoliaSearchManager extends Singleton {

    protected $index_name = '';

    /**
     * @var null
     */
    protected static $instance = null;


    /**
     * @var string
     */

    protected $baseEndpoint = null;

   
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \Algolia\AlgoliaSearch\SearchIndex
     */
    protected $index;

    protected $hitsPerPage = 10; 

    /**
     * @var Request
     */
    public $request;

    public $indexSettings = [];
      
    public $searchQuery = [
        'page' => '0',
        'hitsPerPage' => 10,
        "filters" => "",
        'sortFacetValuesBy' => "count"
    ];

    /**
     * AlgoliaSearchService constructor.    
     */
    public function __construct() {
        $config            = SearchConfig::create(config('devslocation.algolia.app_key'), config('devslocation.algolia.secret_key'));
        $this->index_name = config('devslocation.algolia.index_name');
        $this->client      = SearchClient::createWithConfig($config);
        $this->index       = $this->client->initIndex($this->index_name);
    }


    public function searchResults($debug = false) {
        $this->createSearchQuery(false);
        if($this->searchQuery['filters'] == ""){
            $queryStr = $this->searchQuery['query'] && $this->searchQuery['query'] != "" ?  $this->searchQuery['query'] :"";
        }else{
            $queryStr = "";
        }
        $this->searchQuery['clickAnalytics'] = false;
        $searchResultResponse = $this->index->search($queryStr, $this->searchQuery);
        $searchResultResponse = $this->formatKeys($searchResultResponse);
        return $searchResultResponse;
    }


    public function searchForFacetValues($attribute, $query) {
        $this->createSearchQuery(false);
        $queryStr = "";
        $this->searchQuery['clickAnalytics'] = true;
        $searchResultResponse = $this->index->searchForFacetValues($attribute, $query, $this->searchQuery);
        $searchResultResponse = $this->formatKeys($searchResultResponse);
        return $searchResultResponse;
    }

    public function formatKeys($rawResult) {
        $finalresult = [];
        if (isset($rawResult['nbHits'])) {
            $finalresult['num_of_results'] = $rawResult['nbHits'];
            unset($rawResult['nbHits']);
        }
        if (isset($rawResult['hits'])) {
            $finalresult['results'] = $rawResult['hits'];
            unset($rawResult['hits']);
        }
        return $finalresult;
    }
   
    public function createSearchQuery($addFacets = false, $autoComplete = false) {
        if (isset($this->searchQuery['filters']) && is_array($this->searchQuery['filters'])) {
            $filters = $this->searchQuery['filters'];
            $this->searchQuery["filters"] = "";
            foreach ($filters as $index => $filter) {
                $this->searchQuery["filters"] .= $filter . " AND ";
            }
            $this->searchQuery["filters"] = rtrim($this->searchQuery["filters"], "AND ");
        }
        // add some mandatory facets in query string
        if ($addFacets) {
            $this->addMandatoryFacetsInSearchQuery();
        }
    }

    public function addMandatoryFacetsInSearchQuery() {
        $this->searchQuery['facets'] =  array(
            'urban_area',
            'state',
            'region'
        );
    }

    public function offset($offset) {
        $page                      = (int)($offset / $this->hitsPerPage);
        $this->searchQuery['page'] = $page;
        return $this;
    }

    public function limit($limit) {
        $this->searchQuery['hitsPerPage'] = ($limit) ?: 10;  
        return $this;
    }
    
    public function filter($attribute,$value) {
        if (!is_array($this->searchQuery['filters'])) {
            $this->searchQuery['filters'] = [];
        }
        $query = trim($value);
        if ($query != '') {
            $this->searchQuery['filters'][] = "$attribute:'$query'";
            
        }
        return $this;
    }

    public function unsetFilters() {
        $this->searchQuery['filters'] = [];
        return $this;
    }


    public function query($queryTerm) {
        if ($queryTerm) {
            $this->searchQuery['query'] = $queryTerm;
        }
        return $this;
    }



    public function initializeFilters()
    {
        if (!is_array($this->searchQuery['filters'])) {
            $this->searchQuery['filters'] = [];
        }
        return $this;
    }
}

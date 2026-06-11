<?php

declare(strict_types=1);

namespace MoriElasticSearch\Service\Search;

use MoriElasticSearch\Struct\PdfSearchResultStruct;
use Shopware\Core\Framework\Context;

class SearchCoordinator
{
    private ElasticsearchSearch $elasticsearchSearch;
    private DatabaseSearch $databaseSearch;

    public function __construct(
        ElasticsearchSearch $elasticsearchSearch,
        DatabaseSearch      $databaseSearch
    )
    {
        $this->elasticsearchSearch = $elasticsearchSearch;
        $this->databaseSearch = $databaseSearch;
    }

    public function search(string $term, Context $context): PdfSearchResultStruct
    {

        $results = $this->elasticsearchSearch->search($term);
        if (empty($results)) {
            $results = $this->databaseSearch->search($term, $context);

        }

        $resultStruct = new PdfSearchResultStruct();
        $resultStruct->setResults($results);
        $resultStruct->setTotal(count($results));

        return $resultStruct;
    }
}

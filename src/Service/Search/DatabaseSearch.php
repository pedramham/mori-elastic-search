<?php

namespace MoriElasticSearch\Service\Search;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;

class DatabaseSearch
{
    private EntityRepository $pdfElasticSearchRepository;

    public function __construct(EntityRepository $pdfElasticSearchRepository)
    {
        $this->pdfElasticSearchRepository = $pdfElasticSearchRepository;
    }

    public function search(string $keyword, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new OrFilter([
                new ContainsFilter('title', $keyword),
                new ContainsFilter('description', $keyword),
            ])
        );

        $result = $this->pdfElasticSearchRepository->search($criteria, $context);
        return $result->getEntities()->getElements();
    }
}

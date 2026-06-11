<?php

declare(strict_types=1);

namespace MoriElasticSearch\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class DatabaseStorage implements ConvertPdfInterface
{
    private EntityRepository $repository;

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function save(array $data): void
    {
        $context = Context::createDefaultContext();

        $this->repository->create([
            [
                'title' => $data['title'],
                'mediaId' => $data['mediaId'],
                'description' => $data['description'],
                'path' => $data['url']
            ]
        ], $context);
    }

    public function exists(string $mediaId): bool
    {
        $context = Context::createDefaultContext();
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('mediaId', $mediaId));

        $result = $this->repository->search($criteria, $context);
        return $result->getTotal() > 0;
    }
}
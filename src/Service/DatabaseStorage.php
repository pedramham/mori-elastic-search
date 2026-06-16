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
                'path' => $data['url'],
            ],
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

    public function delete(string $mediaId): bool
    {
        $context = Context::createDefaultContext();

        try {
            // Find the record by mediaId
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('mediaId', $mediaId));
            $criteria->setLimit(1);

            $ids = $this->repository->searchIds($criteria, $context)->getIds();

            if (! empty($ids)) {
                $this->repository->delete([
                    [
                        'id' => $ids[0],
                    ],
                ], $context);
                return true;
            }

            return false; // Record not found
        } catch (\Exception $e) {
            return false; // Delete failed
        }
    }
}

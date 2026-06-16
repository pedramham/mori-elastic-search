<?php

declare(strict_types=1);

namespace MoriElasticSearch\Core\Content\PdfElasticSearch;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(PdfElasticSearchEntity $entity)
 * @method void set(string $key, PdfElasticSearchEntity $entity)
 * @method PdfElasticSearchEntity[] getIterator()
 * @method PdfElasticSearchEntity[] getElements()
 * @method PdfElasticSearchEntity|null get(string $key)
 * @method PdfElasticSearchEntity|null first()
 * @method PdfElasticSearchEntity|null last()
 */
class PdfElasticSearchCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return PdfElasticSearchEntity::class;
    }
}

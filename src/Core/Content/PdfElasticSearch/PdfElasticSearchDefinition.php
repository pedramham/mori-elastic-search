<?php

declare(strict_types=1);

namespace MoriElasticSearch\Core\Content\PdfElasticSearch;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PdfElasticSearchDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'pdf_elastic_search';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return PdfElasticSearchEntity::class;
    }

    public function getCollectionClass(): string
    {
        return PdfElasticSearchCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new IdField('media_id', 'mediaId'))->addFlags(new Required()),
            (new StringField('title', 'title')),
            (new StringField('path', 'path')),
            (new LongTextField('description', 'description')),
            (new BoolField('active', 'active')),
        ]);
    }
}

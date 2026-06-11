<?php declare(strict_types=1);

namespace MoriElasticSearch\Core\Content\PdfElasticSearch;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PdfElasticSearchEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $title;

    protected ?string $mediaId;

    protected ?string $path;

    protected ?string $description;

    protected bool $active;

    public function getMedia(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}

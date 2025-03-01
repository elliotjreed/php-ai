<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class ImageSource
{
    private ImageSourceType $type;
    private ?MediaType $mediaType = null;
    private ?string $data = null;
    private ?string $url = null;
    private ?ImageUrl $imageUrl = null;

    public function getType(): ImageSourceType
    {
        return $this->type;
    }

    public function setType(ImageSourceType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMediaType(): ?MediaType
    {
        return $this->mediaType;
    }

    public function setMediaType(?MediaType $mediaType): self
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getImageUrl(): ?ImageUrl
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?ImageUrl $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }
}

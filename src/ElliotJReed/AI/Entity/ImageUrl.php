<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class ImageUrl
{
    private string $url;
    private ImageUrlDetail $detail = ImageUrlDetail::LOW;

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getDetail(): ImageUrlDetail
    {
        return $this->detail;
    }

    public function setDetail(ImageUrlDetail $detail): self
    {
        $this->detail = $detail;

        return $this;
    }
}

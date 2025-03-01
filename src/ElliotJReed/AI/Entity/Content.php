<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class Content
{
    private ContentType $type = ContentType::TEXT;
    private ?string $text = null;
    private ?ImageSource $source = null;

    public function getType(): ContentType
    {
        return $this->type;
    }

    public function setType(ContentType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getSource(): ?ImageSource
    {
        return $this->source;
    }

    public function setSource(?ImageSource $source): self
    {
        $this->source = $source;

        return $this;
    }
}

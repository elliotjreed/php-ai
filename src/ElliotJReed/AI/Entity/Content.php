<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class Content
{
    private ContentType $type;
    private string $text;

    public function getType(): ContentType
    {
        return $this->type;
    }

    public function setType(ContentType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class Example
{
    private string $prompt;
    private string $response;

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function setPrompt(string $prompt): self
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function setResponse(string $response): self
    {
        $this->response = $response;

        return $this;
    }
}

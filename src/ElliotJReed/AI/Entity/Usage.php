<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class Usage
{
    private int $inputTokens;
    private int $outputTokens;

    public function getOutputTokens(): int
    {
        return $this->outputTokens;
    }

    public function setOutputTokens(int $outputTokens): self
    {
        $this->outputTokens = $outputTokens;

        return $this;
    }

    public function getInputTokens(): int
    {
        return $this->inputTokens;
    }

    public function setInputTokens(int $inputTokens): self
    {
        $this->inputTokens = $inputTokens;

        return $this;
    }
}

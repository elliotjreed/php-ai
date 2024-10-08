<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class Request
{
    private ?string $role = null;
    private float $temperature = 1.0;
    private int $maximumTokens = 2000;
    private ?string $context = null;
    private ?string $instructions = null;
    private ?string $input = null;
    private ?string $data = null;
    private array $examples = [];
    /**
     * @var \ElliotJReed\AI\Entity\History[]
     */
    private array $history = [];

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getMaximumTokens(): int
    {
        return $this->maximumTokens;
    }

    public function setMaximumTokens(int $maximumTokens): self
    {
        $this->maximumTokens = $maximumTokens;

        return $this;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(?string $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): self
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getInput(): ?string
    {
        return $this->input;
    }

    public function setInput(?string $input): self
    {
        $this->input = $input;

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

    /**
     * @return \ElliotJReed\AI\Entity\Example[]
     */
    public function getExamples(): array
    {
        return $this->examples;
    }

    /**
     * @param \ElliotJReed\AI\Entity\Example[] $example
     */
    public function setExamples(array $example): self
    {
        $this->examples = $example;

        return $this;
    }

    /**
     * @return \ElliotJReed\AI\Entity\History[]
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * @param \ElliotJReed\AI\Entity\History[] $history
     */
    public function setHistory(array $history): self
    {
        $this->history = $history;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class Request
{
    private ?string $systemPrompt = null;
    private float $temperature = 1.0;
    private int $maximumTokens = 10000;
    private string | StructuredPrompt | null $textPrompt = null;
    /**
     * @var string[]
     */
    private array $images = [];

    /**
     * @var History[]
     */
    private array $history = [];

    /**
     * @return string|null Instructions to the model that are prioritised ahead of user messages
     */
    public function getSystemPrompt(): ?string
    {
        return $this->systemPrompt;
    }

    /**
     * @param string|null $systemPrompt Instructions to the model that are prioritised ahead of user messages
     *
     * @return $this
     */
    public function setSystemPrompt(?string $systemPrompt): self
    {
        $this->systemPrompt = $systemPrompt;

        return $this;
    }

    /**
     * @return float Amount of randomness injected into the response. Defaults to 1.0. Ranges from 0.0 to 1.0. Use temperature closer to 0.0 for analytical / multiple choice, and closer to 1.0 for creative and generative tasks. Note that even with temperature of 0.0, the results will not be fully deterministic.
     */
    public function getTemperature(): float
    {
        return $this->temperature;
    }

    /**
     * @param float $temperature Amount of randomness injected into the response. Defaults to 1.0. Ranges from 0.0 to 1.0. Use temperature closer to 0.0 for analytical / multiple choice, and closer to 1.0 for creative and generative tasks. Note that even with temperature of 0.0, the results will not be fully deterministic.
     *
     * @return $this
     */
    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    /**
     * @return int The maximum number of tokens to generate before stopping. Note that the models may stop before reaching this maximum. This parameter only specifies the absolute maximum number of tokens to generate.
     */
    public function getMaximumTokens(): int
    {
        return $this->maximumTokens;
    }

    /**
     * @param int $maximumTokens The maximum number of tokens to generate before stopping. Note that the models may stop before reaching this maximum. This parameter only specifies the absolute maximum number of tokens to generate.
     *
     * @return $this
     */
    public function setMaximumTokens(int $maximumTokens): self
    {
        $this->maximumTokens = $maximumTokens;

        return $this;
    }

    /**
     * @return History[] The chat history between the AI assistant and the user
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * @param History[] $history The chat history between the AI assistant and the user
     */
    public function setHistory(array $history): self
    {
        $this->history = $history;

        return $this;
    }

    public function getTextPrompt(): StructuredPrompt | string | null
    {
        return $this->textPrompt;
    }

    public function setTextPrompt(StructuredPrompt | string | null $textPrompt): self
    {
        $this->textPrompt = $textPrompt;

        return $this;
    }

    /**
     * @return string[] An array of image URLs or base64 encoded image content
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param string[] $images An array of image URLs or base64 encoded image content
     *
     * @return $this
     */
    public function setImages(array $images): self
    {
        $this->images = $images;

        return $this;
    }
}

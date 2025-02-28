<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class Request
{
    private ?string $systemPrompt = null;
    private float $temperature = 1.0;
    private int $maximumTokens = 10000;
    private ?string $context = null;
    private ?string $instructions = null;
    private ?string $userInput = null;
    private ?string $data = null;
    /**
     * @var string[]
     */
    private array $examples = [];
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
     * @return string|null Background information sent in the user prompt
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * @param string|null $context Background information sent in the user prompt
     *
     * @return $this
     */
    public function setContext(?string $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return string|null Instructions sent in the user prompt (higher level trusted instructions should be set in the system prompt: setSystemPrompt())
     */
    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    /**
     * @param string|null $instructions Instructions sent in the user prompt (higher level trusted instructions should be set in the system prompt: setSystemPrompt())
     *
     * @return $this
     */
    public function setInstructions(?string $instructions): self
    {
        $this->instructions = $instructions;

        return $this;
    }

    /**
     * @return string|null User input sent in the user prompt (this could be untrusted input, eg. from a web form or live chat)
     */
    public function getUserInput(): ?string
    {
        return $this->userInput;
    }

    /**
     * @param string|null $userInput User input sent in the user prompt (this could be untrusted input, eg. from a web form or live chat)
     *
     * @return $this
     */
    public function setUserInput(?string $userInput): self
    {
        $this->userInput = $userInput;

        return $this;
    }

    /**
     * @return string|null Data sent in the user prompt (eg. CSV contents)
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @param string|null $data Data sent in the user prompt (eg. CSV contents)
     *
     * @return $this
     */
    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string[] Examples sent in the user prompt (eg. existing FAQs for a chat bot)
     */
    public function getExamples(): array
    {
        return $this->examples;
    }

    /**
     * @param string[] $example Examples sent in the user prompt (eg. existing FAQs for a chat bot)
     */
    public function setExamples(array $example): self
    {
        $this->examples = $example;

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
}

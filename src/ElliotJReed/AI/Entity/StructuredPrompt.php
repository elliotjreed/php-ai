<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class StructuredPrompt
{
    private ?string $context = null;
    private ?string $instructions = null;
    private ?string $userInput = null;
    private ?string $data = null;
    /**
     * @var string[]
     */
    private array $examples = [];

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
}

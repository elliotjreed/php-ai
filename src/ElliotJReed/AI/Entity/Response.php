<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class Response
{
    private string $type;
    private string $id;
    private Role $role;
    private string $model;
    private string $content;
    private string $stopReason;
    private ?string $stopSequence = null;
    private Usage $usage;
    /**
     * @var History[]
     */
    private array $history = [];

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string The reason that the AI assistant stopped output (ie. the model reached a natural stopping point, the maximum tokens set was reached, the stop sequence was generated)
     */
    public function getStopReason(): string
    {
        return $this->stopReason;
    }

    /**
     * @param string $stopReason The reason that the AI assistant stopped output (ie. the model reached a natural stopping point, the maximum tokens set was reached, the stop sequence was generated)
     *
     * @return $this
     */
    public function setStopReason(string $stopReason): self
    {
        $this->stopReason = $stopReason;

        return $this;
    }

    /**
     * @return string|null Which custom stop sequence was generated, if any
     */
    public function getStopSequence(): ?string
    {
        return $this->stopSequence;
    }

    /**
     * @param string|null $stopSequence Which custom stop sequence was generated, if any
     *
     * @return $this
     */
    public function setStopSequence(?string $stopSequence): self
    {
        $this->stopSequence = $stopSequence;

        return $this;
    }

    /**
     * @return Usage Billing and rate-limit usage
     */
    public function getUsage(): Usage
    {
        return $this->usage;
    }

    /**
     * @param Usage $usage Billing and rate-limit usage
     *
     * @return $this
     */
    public function setUsage(Usage $usage): self
    {
        $this->usage = $usage;

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

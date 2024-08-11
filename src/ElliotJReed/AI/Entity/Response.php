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
     * @var \ElliotJReed\AI\Entity\History[]
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

    public function getStopReason(): string
    {
        return $this->stopReason;
    }

    public function setStopReason(string $stopReason): self
    {
        $this->stopReason = $stopReason;

        return $this;
    }

    public function getStopSequence(): ?string
    {
        return $this->stopSequence;
    }

    public function setStopSequence(?string $stopSequence): self
    {
        $this->stopSequence = $stopSequence;

        return $this;
    }

    public function getUsage(): Usage
    {
        return $this->usage;
    }

    public function setUsage(Usage $usage): self
    {
        $this->usage = $usage;

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

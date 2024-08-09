<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class Response
{
    private string $type;
    private string $id;
    private string $role;
    private string $model;
    private array $contents = [];
    private string $stopReason;
    private ?string $stopSequence = null;
    private Usage $usage;

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

    /**
     * @return "user"|"assistant"
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param "user"|"assistant" $role
     */
    public function setRole(string $role): self
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

    /**
     * @return \ElliotJReed\AI\Entity\Content[]
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    public function setContents(array $contents): self
    {
        $this->contents = $contents;

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
}

<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class History
{
    private Role $role;
    /**
     * @var \ElliotJReed\AI\Entity\Content[]
     */
    private array $contents;

    /**
     * @return \ElliotJReed\AI\Entity\Content[]
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * @param \ElliotJReed\AI\Entity\Content[] $contents
     */
    public function setContents(array $contents): self
    {
        $this->contents = $contents;

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

    public function toArray(): array
    {
        $messageHistory = [];
        foreach ($this->getContents() as $historyContents) {
            $messageHistory[] = [
                'type' => $historyContents->getType()->value,
                'text' => $historyContents->getText()
            ];
        }

        return ['role' => $this->role->value, 'content' => $messageHistory];
    }
}

<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

use ElliotJReed\AI\Utility\HistoryFormatter;

class History
{
    private Role $role;
    /**
     * @var Content[]
     */
    private array $contents;

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * @param Content[] $contents
     *
     * @return $this
     */
    public function setContents(array $contents): self
    {
        $this->contents = $contents;

        return $this;
    }

    public function toArray(): array
    {
        $contents = [];
        foreach ($this->contents as $content) {
            $contents[] = HistoryFormatter::toArray($content);
        }

        return [
            'role' => $this->role->value,
            'content' => $contents
        ];
    }
}

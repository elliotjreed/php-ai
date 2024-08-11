<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

class History
{
    private Role $role;
    private string $content;

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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
        return [
            'role' => $this->role->value,
            'content' => [[
                'type' => 'text',
                'text' => $this->content
            ]]
        ];
    }
}

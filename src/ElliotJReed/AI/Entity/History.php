<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

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
            if (ContentType::TEXT === $content->getType()) {
                $contents[] = [
                    'type' => $content->getType(),
                    'text' => $content->getText()
                ];
            }

            if (ContentType::IMAGE === $content->getType()) {
                if (ImageSourceType::BASE64 === $content->getSource()->getType()) {
                    $contents[] = [
                        'type' => $content->getType(),
                        'source' => [
                            'type' => $content->getSource()->getType()->value,
                            'media_type' => $content->getSource()->getMediaType()->value,
                            'data' => $content->getSource()->getData()
                        ]
                    ];
                }

                if (ImageSourceType::URL === $content->getSource()->getType()) {
                    $contents[] = [
                        'type' => $content->getType(),
                        'source' => [
                            'type' => $content->getSource()->getType()->value,
                            'url' => $content->getSource()->getUrl()
                        ]
                    ];
                }
            }

            if (ContentType::IMAGE_URL === $content->getType()) {
                $contents[] = [
                    'type' => $content->getType(),
                    'image_url' => [
                        'url' => $content->getSource()->getImageUrl()->getUrl(),
                        'detail' => $content->getSource()->getImageUrl()->getDetail()
                    ]
                ];
            }
        }

        return [
            'role' => $this->role->value,
            'content' => $contents
        ];
    }
}

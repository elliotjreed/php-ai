<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Utility;

use ElliotJReed\AI\Entity\Content;
use ElliotJReed\AI\Entity\ContentType;
use ElliotJReed\AI\Entity\ImageSourceType;

final class HistoryFormatter
{
    public static function toArray(Content $requestContent): array
    {
        if (ContentType::TEXT === $requestContent->getType()) {
            return [
                'type' => $requestContent->getType(),
                'text' => $requestContent->getText()
            ];
        }

        if (ContentType::IMAGE === $requestContent->getType()) {
            if (ImageSourceType::BASE64 === $requestContent->getSource()->getType()) {
                return [
                    'type' => $requestContent->getType(),
                    'source' => [
                        'type' => $requestContent->getSource()->getType()->value,
                        'media_type' => $requestContent->getSource()->getMediaType()->value,
                        'data' => $requestContent->getSource()->getData()
                    ]
                ];
            }

            if (ImageSourceType::URL === $requestContent->getSource()->getType()) {
                return [
                    'type' => $requestContent->getType(),
                    'source' => [
                        'type' => $requestContent->getSource()->getType()->value,
                        'url' => $requestContent->getSource()->getUrl()
                    ]
                ];
            }
        }

        if (ContentType::IMAGE_URL === $requestContent->getType()) {
            return [
                'type' => $requestContent->getType(),
                'image_url' => [
                    'url' => $requestContent->getSource()->getImageUrl()->getUrl(),
                    'detail' => $requestContent->getSource()->getImageUrl()->getDetail()->value
                ]
            ];
        }

        return [];
    }
}

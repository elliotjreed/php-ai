<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

enum MediaType: string
{
    case JPEG = 'image/jpeg';
    case PNG = 'image/png';
    case GIF = 'image/gif';
    case WEBP = 'image/webp';
}

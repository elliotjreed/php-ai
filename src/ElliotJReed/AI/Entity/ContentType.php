<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

enum ContentType: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case IMAGE_URL = 'image_url';
}

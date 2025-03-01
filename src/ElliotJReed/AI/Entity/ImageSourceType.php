<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

enum ImageSourceType: string
{
    case BASE64 = 'base64';
    case URL = 'url';
}

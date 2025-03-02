<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Exception;

final class UnsupportedImageMimeTypeException extends AIRequestException
{
    protected $message = 'Image mimetype must be one of: image/jpeg, image/png, image/gif, image/webp';
}

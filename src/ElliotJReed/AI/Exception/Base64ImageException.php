<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Exception;

final class Base64ImageException extends AIRequestException
{
    protected $message = 'Unable to decode base64 image';
}

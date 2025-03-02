<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Utility;

use ElliotJReed\AI\Exception\Base64ImageException;
use finfo;

final class MimeType
{
    public static function fromBase64EncodedFile(string $base64EncodedFile): string
    {
        $decodedImage = \base64_decode($base64EncodedFile);

        if (!$decodedImage) {
            throw new Base64ImageException();
        }

        $fileInfo = new finfo(\FILEINFO_MIME_TYPE);

        return $fileInfo->buffer($decodedImage);
    }
}

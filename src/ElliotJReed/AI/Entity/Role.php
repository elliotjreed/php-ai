<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Entity;

enum Role: string
{
    case USER = 'user';
    case ASSISTANT = 'assistant';
}

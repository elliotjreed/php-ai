<?php

declare(strict_types=1);

namespace ElliotJReed\AI;

use ElliotJReed\AI\Entity\Request;
use ElliotJReed\AI\Entity\Response;

interface PromptInterface
{
    public function send(Request $request): Response;
}

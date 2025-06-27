<?php

declare(strict_types=1);

namespace ElliotJReed\AI;

use ElliotJReed\AI\Entity\Content;
use ElliotJReed\AI\Entity\ContentType;
use ElliotJReed\AI\Entity\Request;
use ElliotJReed\AI\Entity\Response;
use ElliotJReed\AI\Entity\StructuredPrompt;
use ElliotJReed\AI\Utility\StructuredPromptFormatter;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

abstract class AbstractPrompt implements PromptInterface
{
    public function __construct(
        protected readonly string $apiKey,
        protected readonly string $model,
        protected readonly ClientInterface $client = new Client()
    ) {
    }

    abstract public function send(Request $request): Response;

    protected function getTextPrompt(StructuredPrompt | string $textPrompt): ?Content
    {
        if ($textPrompt instanceof StructuredPrompt) {
            return (new Content())
                ->setType(ContentType::TEXT)
                ->setText(StructuredPromptFormatter::toXml($textPrompt));
        }

        return (new Content())
            ->setType(ContentType::TEXT)
            ->setText($textPrompt);
    }
}

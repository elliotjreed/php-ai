<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Claude;

use ElliotJReed\AI\Entity\Content;
use ElliotJReed\AI\Entity\ContentType;
use ElliotJReed\AI\Entity\History;
use ElliotJReed\AI\Entity\Response;
use ElliotJReed\AI\Entity\Role;
use ElliotJReed\AI\Entity\Usage;
use ElliotJReed\AI\Exception\ClaudeHttpClientException;
use ElliotJReed\AI\Exception\ClaudeRequestException;
use ElliotJReed\AI\Exception\ClaudeResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;

class Prompt extends \ElliotJReed\AI\Prompt
{
    private const CLAUDE_URL = 'https://api.anthropic.com/v1/messages';
    private const ANTHROPIC_VERSION = '2023-06-01';

    protected function request(array $body): array
    {
        try {
            $response = $this->client->request(
                'POST',
                self::CLAUDE_URL,
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'anthropic-version' => self::ANTHROPIC_VERSION,
                        'x-api-key' => $this->apiKey
                    ],
                    'options' => [
                        RequestOptions::HTTP_ERRORS => false
                    ],
                    'json' => $body
                ]
            );

            $responseBody = $response->getBody()->getContents();
        } catch (RequestException $exception) {
            if ($exception->hasResponse()) {
                $responseBody = $exception->getResponse()->getBody()->getContents();
            } else {
                throw new ClaudeRequestException('Claude API request exception', previous: $exception);
            }
        } catch (ClientExceptionInterface $exception) {
            throw new ClaudeHttpClientException('Claude API HTTP client exception', previous: $exception);
        }

        try {
            $decoded = \json_decode($responseBody, true, 8, \JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ClaudeResponseException('Unexpected Claude API response format', previous: $exception);
        }

        if ('error' === $decoded['type']) {
            throw new ClaudeResponseException($decoded['error']['type'] . ' (' . $decoded['error']['message'] . ')');
        }

        return $decoded;
    }

    protected function getResponse(array $requestBody, array $history): Response
    {
        $decoded = $this->request($requestBody);

        return (new Response())
            ->setId($decoded['id'])
            ->setType($decoded['type'])
            ->setRole(Role::from($decoded['role']))
            ->setModel($decoded['model'])
            ->setContent($decoded['content'][0]['text'])
            ->setStopReason($decoded['stop_reason'])
            ->setStopSequence($decoded['stop_sequence'])
            ->setUsage((new Usage())
                ->setInputTokens($decoded['usage']['input_tokens'])
                ->setOutputTokens($decoded['usage']['output_tokens']))
            ->setHistory([...$history, (new History())
                ->setRole(Role::from($decoded['role']))
                ->setContents([
                    (new Content())->setType(ContentType::TEXT)->setText($decoded['content'][0]['text'])
                ])
            ]);
    }
}

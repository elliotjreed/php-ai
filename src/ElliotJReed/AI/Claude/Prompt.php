<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Claude;

use ElliotJReed\AI\AbstractPrompt;
use ElliotJReed\AI\Entity\Content;
use ElliotJReed\AI\Entity\ContentType;
use ElliotJReed\AI\Entity\History;
use ElliotJReed\AI\Entity\ImageSource;
use ElliotJReed\AI\Entity\ImageSourceType;
use ElliotJReed\AI\Entity\MediaType;
use ElliotJReed\AI\Entity\Request;
use ElliotJReed\AI\Entity\Response;
use ElliotJReed\AI\Entity\Role;
use ElliotJReed\AI\Entity\StructuredPrompt;
use ElliotJReed\AI\Entity\Usage;
use ElliotJReed\AI\Exception\ClaudeHttpClientException;
use ElliotJReed\AI\Exception\ClaudeRequestException;
use ElliotJReed\AI\Exception\ClaudeResponseException;
use ElliotJReed\AI\Exception\UnsupportedImageMimeTypeException;
use ElliotJReed\AI\Utility\MimeType;
use ElliotJReed\AI\Utility\StructuredPromptFormatter;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;

class Prompt extends AbstractPrompt
{
    private const string CLAUDE_URL = 'https://api.anthropic.com/v1/messages';
    private const string ANTHROPIC_VERSION = '2023-06-01';

    public function send(Request $request): Response
    {
        /** @var Content[] $contents */
        $contents = [];
        if (null !== $request->getTextPrompt()) {
            $contents[] = $this->getTextPrompt($request->getTextPrompt());
        }

        foreach ($request->getImages() as $image) {
            if (\str_starts_with($image, 'http')) {
                $contents[] = (new Content())
                    ->setType(ContentType::IMAGE)
                    ->setSource((new ImageSource())
                        ->setType(ImageSourceType::URL)
                        ->setUrl($image));
            } else {
                $mimeType = MimeType::fromBase64EncodedFile($image);

                if (!\in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                    throw new UnsupportedImageMimeTypeException();
                }

                $contents[] = (new Content())
                    ->setType(ContentType::IMAGE)
                    ->setSource((new ImageSource())
                        ->setType(ImageSourceType::BASE64)
                        ->setMediaType(MediaType::from($mimeType))
                        ->setData($image));
            }
        }

        $history = $request->getHistory();
        $history[] = (new History())
            ->setRole(Role::USER)
            ->setContents($contents);

        $requestHistory = [];
        foreach ($history as $historyItem) {
            $requestHistory[] = $historyItem->toArray();
        }

        $systemPrompt = $request->getSystemPrompt();
        if ($systemPrompt instanceof StructuredPrompt) {
            $systemPrompt = StructuredPromptFormatter::toXml($systemPrompt);
        }

        $requestBody = [
            'model' => $this->model,
            'max_tokens' => $request->getMaximumTokens(),
            'temperature' => $request->getTemperature(),
            'system' => $systemPrompt,
            'messages' => $requestHistory
        ];

        return $this->getResponse($requestBody, $history);
    }

    private function getResponse(array $requestBody, array $history): Response
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

    private function request(array $body): array
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
}

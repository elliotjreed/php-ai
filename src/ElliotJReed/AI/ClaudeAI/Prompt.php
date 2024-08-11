<?php

declare(strict_types=1);

namespace ElliotJReed\AI\ClaudeAI;

use ElliotJReed\AI\Entity\Content;
use ElliotJReed\AI\Entity\ContentType;
use ElliotJReed\AI\Entity\History;
use ElliotJReed\AI\Entity\Request;
use ElliotJReed\AI\Entity\Response;
use ElliotJReed\AI\Entity\Role;
use ElliotJReed\AI\Entity\Usage;
use ElliotJReed\AI\Exception\ClaudeHttpClientException;
use ElliotJReed\AI\Exception\ClaudeRequestException;
use ElliotJReed\AI\Exception\ClaudeResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class Prompt extends \ElliotJReed\AI\Prompt
{
    private const CLAUDE_URL = 'https://api.anthropic.com/v1/messages';
    private const ANTHROPIC_VERSION = '2023-06-01';

    public function send(Request $request): Response
    {
        $requestContent = $this->buildRequest($request);

        $response = (new Response())
            ->setHistory([
                ...$request->getHistory(),
                (new History())
                    ->setRole(Role::USER)
                    ->setContents([(new Content())
                        ->setType($requestContent->getType())
                        ->setText($requestContent->getText())])
            ]);

        $messageHistory = [];
        foreach ($response->getHistory() as $history) {
            $messageHistory[] = $history->toArray();
        }

        $requestBody = [
            'model' => $this->model,
            'max_tokens' => $request->getMaximumTokens(),
            'temperature' => $request->getTemperature(),
            'messages' => $messageHistory
        ];

        if (null !== $request->getRole() && '' !== \trim($request->getRole())) {
            $requestBody['system'] = $request->getRole();
        }

        $decoded = \json_decode($this->request($requestBody)->getBody()->getContents(), true, 8, \JSON_THROW_ON_ERROR);

        if ('error' === $decoded['type']) {
            throw new ClaudeResponseException($decoded['error']['type'] . ' (' . $decoded['error']['message'] . ')');
        }

        $responseContents = [];
        foreach ($decoded['content'] as $item) {
            $responseContents[] = (new Content())
                ->setType(ContentType::from($item['type']))
                ->setText($item['text']);
        }

        return $response
            ->setId($decoded['id'])
            ->setType($decoded['type'])
            ->setRole(Role::from($decoded['role']))
            ->setModel($decoded['model'])
            ->setContents($responseContents)
            ->setStopReason($decoded['stop_reason'])
            ->setStopSequence($decoded['stop_sequence'])
            ->setUsage((new Usage())
                ->setInputTokens($decoded['usage']['input_tokens'])
                ->setOutputTokens($decoded['usage']['output_tokens']))
            ->addHistory((new History())
                ->setRole(Role::ASSISTANT)
                ->setContents($responseContents));
    }

    protected function request(array $body): ResponseInterface
    {
        try {
            return $this->client->request(
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
        } catch (RequestException $exception) {
            if ($exception->hasResponse()) {
                return $exception->getResponse();
            }

            throw new ClaudeRequestException('Claude API request exception', previous: $exception);
        } catch (ClientExceptionInterface $exception) {
            throw new ClaudeHttpClientException('Claude API HTTP client exception', previous: $exception);
        }
    }
}

<?php

declare(strict_types=1);

namespace ElliotJReed\AI\ChatGPT;

use ElliotJReed\AI\Entity\History;
use ElliotJReed\AI\Entity\Response;
use ElliotJReed\AI\Entity\Role;
use ElliotJReed\AI\Entity\Usage;
use ElliotJReed\AI\Exception\ChatGPTHttpClientException;
use ElliotJReed\AI\Exception\ChatGPTRequestException;
use ElliotJReed\AI\Exception\ChatGPTResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;

class Prompt extends \ElliotJReed\AI\Prompt
{
    private const CHATGPT_URL = 'https://api.openai.com/v1/chat/completions';

    protected function request(array $body): array
    {
        try {
            $response = $this->client->request(
                'POST',
                self::CHATGPT_URL,
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->apiKey
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
                throw new ChatGPTRequestException('ChatGPT API request exception', previous: $exception);
            }
        } catch (ClientExceptionInterface $exception) {
            throw new ChatGPTHttpClientException('ChatGPT API HTTP client exception', previous: $exception);
        }

        try {
            $decoded = \json_decode($responseBody, true, 8, \JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ChatGPTResponseException('Unexpected ChatGPT API response format', previous: $exception);
        }

        if (\array_key_exists('error', $decoded)) {
            throw new ChatGPTResponseException($decoded['error']['type'] . ' (' . $decoded['error']['message'] . ')');
        }

        return $decoded;
    }

    protected function getResponse(array $requestBody, array $history): Response
    {
        $decoded = $this->request($requestBody);

        return (new Response())
            ->setId($decoded['id'])
            ->setType($decoded['object'])
            ->setRole(Role::from($decoded['choices'][0]['message']['role']))
            ->setModel($decoded['model'])
            ->setContent($decoded['choices'][0]['message']['content'])
            ->setStopReason($decoded['choices'][0]['finish_reason'])
            ->setUsage((new Usage())
                ->setInputTokens($decoded['usage']['prompt_tokens'])
                ->setOutputTokens($decoded['usage']['completion_tokens']))
            ->setHistory([...$history, (new History())
                ->setRole(Role::from($decoded['choices'][0]['message']['role']))
                ->setContent($decoded['choices'][0]['message']['content'])]);
    }
}

<?php

declare(strict_types=1);

namespace ElliotJReed\AI\ChatGPT;

use ElliotJReed\AI\AbstractPrompt;
use ElliotJReed\AI\Entity\Content;
use ElliotJReed\AI\Entity\ContentType;
use ElliotJReed\AI\Entity\History;
use ElliotJReed\AI\Entity\ImageSource;
use ElliotJReed\AI\Entity\ImageUrl;
use ElliotJReed\AI\Entity\ImageUrlDetail;
use ElliotJReed\AI\Entity\Request;
use ElliotJReed\AI\Entity\Response;
use ElliotJReed\AI\Entity\Role;
use ElliotJReed\AI\Entity\Usage;
use ElliotJReed\AI\Exception\ChatGPTHttpClientException;
use ElliotJReed\AI\Exception\ChatGPTRequestException;
use ElliotJReed\AI\Exception\ChatGPTResponseException;
use ElliotJReed\AI\PromptInterface;
use ElliotJReed\AI\Utility\MimeType;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;

class Prompt extends AbstractPrompt implements PromptInterface
{
    private const string CHATGPT_URL = 'https://api.openai.com/v1/chat/completions';

    public function send(Request $request): Response
    {
        $requestHistory = [];
        if (null !== $request->getSystemPrompt()) {
            $requestHistory[] = (new History())
                ->setRole(Role::DEVELOPER)
                ->setContents([
                    $this->getTextPrompt($request->getSystemPrompt())
                ])
                ->toArray();
        }

        /** @var Content[] $contents */
        $contents = [];
        if (null !== $request->getTextPrompt()) {
            $contents[] = $this->getTextPrompt($request->getTextPrompt());
        }

        foreach ($request->getImages() as $image) {
            if (\str_starts_with($image, 'http')) {
                $url = $image;
            } else {
                $mimeType = MimeType::fromBase64EncodedFile($image);

                $url = 'data:' . $mimeType . ';base64,' . $image;
            }

            $contents[] = (new Content())
                ->setType(ContentType::IMAGE_URL)
                ->setSource((new ImageSource())
                    ->setImageUrl((new ImageUrl())
                        ->setUrl($url)
                        ->setDetail(ImageUrlDetail::LOW))); // TODO add image detail
        }

        $history = $request->getHistory();
        $history[] = (new History())
            ->setRole(Role::USER)
            ->setContents($contents);

        foreach ($history as $historyItem) {
            $requestHistory[] = $historyItem->toArray();
        }

        $requestBody = [
            'model' => $this->model,
            'max_tokens' => $request->getMaximumTokens(),
            'temperature' => $request->getTemperature(),
            'messages' => $requestHistory
        ];

        return $this->getResponse($requestBody, $history);
    }

    private function getResponse(array $requestBody, array $history): Response
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
                ->setContents([
                    (new Content())->setType(ContentType::TEXT)->setText($decoded['choices'][0]['message']['content'])
                ])
            ]);
    }

    private function request(array $body): array
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
}

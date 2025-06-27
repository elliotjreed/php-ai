<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Double;

use ElliotJReed\AI\ChatGPT\Prompt;
use ElliotJReed\AI\Entity\Content;
use ElliotJReed\AI\Entity\ContentType;
use ElliotJReed\AI\Entity\History;
use ElliotJReed\AI\Entity\ImageSource;
use ElliotJReed\AI\Entity\ImageSourceType;
use ElliotJReed\AI\Entity\MediaType;
use ElliotJReed\AI\Entity\Request;
use ElliotJReed\AI\Entity\Response;
use ElliotJReed\AI\Entity\Role;
use ElliotJReed\AI\Entity\Usage;
use ElliotJReed\AI\Exception\UnsupportedImageMimeTypeException;
use ElliotJReed\AI\PromptInterface;
use ElliotJReed\AI\Utility\MimeType;

class ClaudePromptMock extends Prompt implements PromptInterface
{
    /**
     * @var string Optional. Set a custom text response.
     */
    public string $response = 'Mocked response from assistant';

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

        return $this->getResponse($history);
    }

    private function getResponse(array $history): Response
    {
        return (new Response())
            ->setId('TESTID')
            ->setType('message')
            ->setRole(Role::ASSISTANT)
            ->setModel($this->model)
            ->setContent($this->response)
            ->setStopReason('length')
            ->setUsage((new Usage())
                ->setInputTokens(25)
                ->setOutputTokens(\str_word_count($this->response)))
            ->setHistory([...$history, (new History())
                ->setRole(Role::ASSISTANT)
                ->setContents([
                    (new Content())->setType(ContentType::TEXT)->setText($this->response)
                ])
            ]);
    }
}

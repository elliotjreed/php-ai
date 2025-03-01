<?php

declare(strict_types=1);

namespace ElliotJReed\AI;

use ElliotJReed\AI\ChatGPT\Prompt as ChatGPTPrompt;
use ElliotJReed\AI\Claude\Prompt as ClaudeAIPrompt;
use ElliotJReed\AI\Entity\Content;
use ElliotJReed\AI\Entity\ContentType;
use ElliotJReed\AI\Entity\History;
use ElliotJReed\AI\Entity\ImageSource;
use ElliotJReed\AI\Entity\ImageSourceType;
use ElliotJReed\AI\Entity\ImageUrl;
use ElliotJReed\AI\Entity\ImageUrlDetail;
use ElliotJReed\AI\Entity\MediaType;
use ElliotJReed\AI\Entity\Request;
use ElliotJReed\AI\Entity\Response;
use ElliotJReed\AI\Entity\Role;
use ElliotJReed\AI\Entity\StructuredPrompt;
use Exception;
use finfo;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use SimpleXMLElement;

abstract class Prompt
{
    public function __construct(
        protected readonly string $apiKey,
        protected readonly string $model,
        protected readonly ClientInterface $client = new Client()
    ) {
    }

    public function send(Request $request): Response
    {
        $requestHistory = [];
        if ($this instanceof ChatGPTPrompt && null !== $request->getSystemPrompt() && '' !== \trim($request->getSystemPrompt())) {
            $requestHistory[] = (new History())
                ->setRole(Role::DEVELOPER)
                ->setContents([
                    (new Content())
                        ->setType(ContentType::TEXT)
                        ->setText($request->getSystemPrompt())
                ])
                ->toArray();
        }

        /** @var History[] $history */
        $history = [];
        foreach ($request->getHistory() as $historyItem) {
            $history[] = $historyItem;
        }

        /** @var Content[] $contents */
        $contents = [];
        if ($request->getTextPrompt() instanceof StructuredPrompt) {
            $contents[] = (new Content())
                ->setType(ContentType::TEXT)
                ->setText($this->buildRequest($request->getTextPrompt()));
        } elseif (null !== $request->getTextPrompt()) {
            $contents[] = (new Content())
                ->setType(ContentType::TEXT)
                ->setText($request->getTextPrompt());
        }

        foreach ($request->getImages() as $image) {
            if ($this instanceof ClaudeAIPrompt) {
                if (\str_starts_with($image, 'http')) {
                    $contents[] = (new Content())
                        ->setType(ContentType::IMAGE)
                        ->setSource((new ImageSource())
                            ->setType(ImageSourceType::URL)
                            ->setUrl($image));
                } else {
                    $decodedImage = \base64_decode($image);

                    if (!$decodedImage) {
                        throw new Exception(); // TODO
                    }

                    $fileInfo = new finfo(\FILEINFO_MIME_TYPE);
                    $mimeType = $fileInfo->buffer($decodedImage);

                    if (!\in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                        throw new Exception(); // TODO
                    }

                    $contents[] = (new Content())
                        ->setType(ContentType::IMAGE)
                        ->setSource((new ImageSource())
                            ->setType(ImageSourceType::BASE64)
                            ->setMediaType(MediaType::from($mimeType))
                            ->setData($image));
                }
            }

            if ($this instanceof ChatGPTPrompt) {
                if (\str_starts_with($image, 'http')) {
                    $url = $image;
                } else {
                    $decodedImage = \base64_decode($image);

                    if (!$decodedImage) {
                        throw new Exception(); // TODO
                    }

                    $fileInfo = new finfo(\FILEINFO_MIME_TYPE);
                    $mimeType = $fileInfo->buffer($decodedImage);

                    $url = 'data:' . $mimeType . ';base64,' . $image;
                }

                $contents[] = (new Content())
                    ->setType(ContentType::IMAGE_URL)
                    ->setSource((new ImageSource())
                        ->setImageUrl((new ImageUrl())
                            ->setUrl($url)
                            ->setDetail(ImageUrlDetail::LOW))); // TODO add image detail
            }
        }

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

        if ($this instanceof ClaudeAIPrompt && null !== $request->getSystemPrompt() && '' !== \trim($request->getSystemPrompt())) {
            $requestBody['system'] = $request->getSystemPrompt();
        }

        return $this->getResponse($requestBody, $history);
    }

    protected function buildRequest(StructuredPrompt $prompt): string
    {
        $xml = new SimpleXMLElement(
            '<prompt />',
        );

        if (null !== $prompt->getContext() && '' !== \trim($prompt->getContext())) {
            $xml->addChild('context', $this->wrapInput($prompt->getContext()));
        }

        if (null !== $prompt->getInstructions() && '' !== \trim($prompt->getInstructions())) {
            $xml->addChild('instructions', $this->wrapInput($prompt->getInstructions()));
        }

        if (null !== $prompt->getUserInput() && '' !== \trim($prompt->getUserInput())) {
            $xml->addChild('user_input', $this->wrapInput($prompt->getUserInput()));
        }

        if (null !== $prompt->getData() && '' !== \trim($prompt->getData())) {
            $xml->addChild('data', $this->wrapInput($prompt->getData()));
        }

        if ([] !== $prompt->getExamples()) {
            $examplesOutput = $xml->addChild('examples');
            foreach ($prompt->getExamples() as $example) {
                $examplesOutput->addChild('example', $this->wrapInput($example));
            }
        }

        return \trim($this->trimXmlDeclaration($this->preserveXmlCdata($xml->asXML())));
    }

    private function trimXmlDeclaration(string $string): string
    {
        $xmlDeclaration = '<?xml version="1.0"?>' . "\n";

        if (\str_starts_with($string, $xmlDeclaration)) {
            return \substr($string, \strlen($xmlDeclaration));
        }

        return $string;
    }

    private function wrapInput(string $input): string
    {
        return '<![CDATA[' . \trim($input) . ']]>';
    }

    private function preserveXmlCdata(string $input): string
    {
        return \str_replace([
            '<context>&lt;![CDATA[',
            '<instructions>&lt;![CDATA[',
            '<user_input>&lt;![CDATA[',
            '<example>&lt;![CDATA[',
            '<data>&lt;![CDATA[',
            ']]&gt;</context>',
            ']]&gt;</instructions>',
            ']]&gt;</user_input>',
            ']]&gt;</example>',
            ']]&gt;</data>'
        ], [
            '<context><![CDATA[',
            '<instructions><![CDATA[',
            '<user_input><![CDATA[',
            '<example><![CDATA[',
            '<data><![CDATA[',
            ']]></context>',
            ']]></instructions>',
            ']]></user_input>',
            ']]></example>',
            ']]></data>'
        ], $input);
    }

    abstract protected function request(array $body): array;

    abstract protected function getResponse(array $requestBody, array $history): Response;
}

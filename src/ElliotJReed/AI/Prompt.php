<?php

declare(strict_types=1);

namespace ElliotJReed\AI;

use ElliotJReed\AI\ChatGPT\Prompt as ChatGPTPrompt;
use ElliotJReed\AI\Claude\Prompt as ClaudeAIPrompt;
use ElliotJReed\AI\Entity\History;
use ElliotJReed\AI\Entity\Request;
use ElliotJReed\AI\Entity\Response;
use ElliotJReed\AI\Entity\Role;
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
                ->setContent($request->getSystemPrompt())
                ->toArray();
        }

        /** @var History[] $history */
        $history = [];
        foreach ($request->getHistory() as $historyItem) {
            $history[] = $historyItem;
        }

        $history[] = (new History())
            ->setRole(Role::USER)
            ->setContent($this->buildRequest($request));

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

    protected function buildRequest(Request $request): string
    {
        $xml = new SimpleXMLElement(
            '<prompt />',
        );

        if (null !== $request->getContext() && '' !== \trim($request->getContext())) {
            $xml->addChild('context', $this->wrapInput($request->getContext()));
        }

        if (null !== $request->getInstructions() && '' !== \trim($request->getInstructions())) {
            $xml->addChild('instructions', $this->wrapInput($request->getInstructions()));
        }

        if (null !== $request->getUserInput() && '' !== \trim($request->getUserInput())) {
            $xml->addChild('user_input', $this->wrapInput($request->getUserInput()));
        }

        if (null !== $request->getData() && '' !== \trim($request->getData())) {
            $xml->addChild('data', $this->wrapInput($request->getData()));
        }

        if ([] !== $request->getExamples()) {
            $examplesOutput = $xml->addChild('examples');
            foreach ($request->getExamples() as $example) {
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

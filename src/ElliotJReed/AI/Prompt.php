<?php

declare(strict_types=1);

namespace ElliotJReed\AI;

use DOMDocument;
use ElliotJReed\AI\Entity\Request;
use ElliotJReed\AI\Exception\AIRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use SimpleXMLElement;

abstract class Prompt
{
    public function __construct(
        protected readonly string $apiKey,
        protected readonly ClientInterface $client = new Client()
    ) {
    }

    protected function buildRequest(Request $request): string
    {
        $xml = new SimpleXMLElement(
            '<prompt xmlns="https://static.elliotjreed.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://static.elliotjreed.com https://static.elliotjreed.com/prompt.xsd" />',
        );

        if (null !== $request->getContext() && '' !== \trim($request->getContext())) {
            $xml->addChild('context', $this->wrapInput($request->getContext()));
        }

        if (null !== $request->getInstructions() && '' !== \trim($request->getInstructions())) {
            $xml->addChild('instructions', $this->wrapInput($request->getInstructions()));
        }

        if (null !== $request->getInput() && '' !== \trim($request->getInput())) {
            $xml->addChild('user_input', $this->wrapInput($request->getInput()));
        }

        if (null !== $request->getData() && '' !== \trim($request->getData())) {
            $xml->addChild('data', $this->wrapInput($request->getData()));
        }

        if ([] !== $request->getExamples()) {
            $examplesOutput = $xml->addChild('examples');
            foreach ($request->getExamples() as $example) {
                $exampleOutput = $examplesOutput->addChild('example');
                $exampleOutput->addChild('example_prompt', $this->wrapInput($example->getPrompt()));
                $exampleOutput->addChild('example_response', $this->wrapInput($example->getResponse()));
            }
        }

        $content = $this->preserveXmlCdata($xml->asXML());

        \libxml_use_internal_errors(true);
        $domDocument = new DOMDocument();
        $domDocument->loadXML($content, \LIBXML_NOWARNING | \LIBXML_NOERROR);
        if (!$domDocument->schemaValidate(__DIR__ . '/../../../prompt-schema.xsd', \LIBXML_NOWARNING | \LIBXML_NOERROR)) {
            throw new AIRequestException('Underlying XML provided to API provider during prompt was invalid.');
        }

        return $content;
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
            '<data>&lt;![CDATA[',
            '<example_prompt>&lt;![CDATA[',
            '<example_response>&lt;![CDATA[',
            ']]&gt;</context>',
            ']]&gt;</instructions>',
            ']]&gt;</user_input>',
            ']]&gt;</data>',
            ']]&gt;</example_prompt>',
            ']]&gt;</example_response>'
        ], [
            '<context><![CDATA[',
            '<instructions><![CDATA[',
            '<user_input><![CDATA[',
            '<data><![CDATA[',
            '<example_prompt><![CDATA[',
            '<example_response><![CDATA[',
            ']]></context>',
            ']]></instructions>',
            ']]></user_input>',
            ']]></data>',
            ']]></example_prompt>',
            ']]></example_response>'
        ], $input);
    }
}

<?php

declare(strict_types=1);

namespace ElliotJReed\AI\Utility;

use ElliotJReed\AI\Entity\StructuredPrompt;
use SimpleXMLElement;

final class StructuredPromptFormatter
{
    public static function toXml(StructuredPrompt $prompt): string
    {
        $xml = new SimpleXMLElement(
            '<prompt />',
        );

        if (null !== $prompt->getContext() && '' !== \trim($prompt->getContext())) {
            $xml->addChild('context', self::wrapInput($prompt->getContext()));
        }

        if (null !== $prompt->getInstructions() && '' !== \trim($prompt->getInstructions())) {
            $xml->addChild('instructions', self::wrapInput($prompt->getInstructions()));
        }

        if (null !== $prompt->getUserInput() && '' !== \trim($prompt->getUserInput())) {
            $xml->addChild('user_input', self::wrapInput($prompt->getUserInput()));
        }

        if (null !== $prompt->getData() && '' !== \trim($prompt->getData())) {
            $xml->addChild('data', self::wrapInput($prompt->getData()));
        }

        if ([] !== $prompt->getExamples()) {
            $examplesOutput = $xml->addChild('examples');
            foreach ($prompt->getExamples() as $example) {
                $examplesOutput->addChild('example', self::wrapInput($example));
            }
        }

        return \trim(self::trimXmlDeclaration(self::preserveXmlCdata($xml->asXML())));
    }

    private static function trimXmlDeclaration(string $string): string
    {
        $xmlDeclaration = '<?xml version="1.0"?>' . "\n";

        if (\str_starts_with($string, $xmlDeclaration)) {
            return \substr($string, \strlen($xmlDeclaration));
        }

        return $string;
    }

    private static function wrapInput(string $input): string
    {
        return '<![CDATA[' . \trim($input) . ']]>';
    }

    private static function preserveXmlCdata(string $input): string
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
}

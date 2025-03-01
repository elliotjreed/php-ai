<?php

require __DIR__ . '/../../vendor/autoload.php';

$prompt = (new ElliotJReed\AI\ChatGPT\Prompt('sk-proj-API-KEY', 'gpt-4o-mini'));

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are responding to customer queries from a web form.')
    ->setTextPrompt((new ElliotJReed\AI\Entity\StructuredPrompt())
        ->setContext('The customer is querying via a form on a e-commerce website based in the United Kingdom.')
        ->setInstructions('Respond using the data from the FAQs in a friendly and accurate way using British English.')
        ->setData('FAQs. Q: Do you offer next day deliver. A: Yes we do, however we do not offer same day delivery.')
        ->setUserInput('Can you deliver today at my address?')
        ->setExamples(['Hello! Unfortunately we are not open on Bank Holidays.']))
    ->setMaximumTokens(300);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL . \PHP_EOL;

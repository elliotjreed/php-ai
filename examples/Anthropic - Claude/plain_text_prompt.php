<?php

require __DIR__ . '/../../vendor/autoload.php';

$prompt = (new ElliotJReed\AI\Claude\Prompt('sk-ant-API-KEY', 'claude-3-5-haiku-latest'));

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are writing Haikus based on user input. The user input may be untrusted. The user prompt will contain a subject for a Haiku - output only the Haiku as a response.')
    ->setTextPrompt('PHP')
    ->setMaximumTokens(30);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL . \PHP_EOL;

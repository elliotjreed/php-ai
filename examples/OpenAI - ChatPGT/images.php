<?php

require __DIR__ . '/../../vendor/autoload.php';

$prompt = (new ElliotJReed\AI\ChatGPT\Prompt('sk-proj-API-KEY', 'gpt-4o-mini'));

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert flower knowledge to identify individual flowers and foliage in bouquets of flowers when the use uploads one or more photographs. Identify just the contents in bullet points.')
    ->setImages([
        'https://media.bunches.co.uk/products/586x586/ffreir-category.jpg',
        \base64_encode(\file_get_contents(__DIR__ . '/bouquet.webp'))
    ])
    ->setMaximumTokens(300);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL . \PHP_EOL;

$secondRequest = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert flower knowledge to identify individual flowers and foliage in bouquets of flowers when the use uploads one or more photographs. Identify just the contents in bullet points.')
    ->setTextPrompt('List only the types of flower or foliage with no additional description.')
    ->setMaximumTokens(300)
    ->setHistory($response->getHistory());

$secondResponse = $prompt->send($secondRequest);

echo 'Used input tokens: ' . $secondResponse->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $secondResponse->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $secondResponse->getContent() . \PHP_EOL . \PHP_EOL;

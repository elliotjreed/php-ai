[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-v2.0%20adopted-ff69b4.svg)](code-of-conduct.md)

A library for interacting with Anthropic / Claude AI and OpenAI / ChatGPT.

**PHP 8.3** and above is supported.

- [Usage](#usage)
    - [Anthropic Claude AI](#anthropic-claude-ai)
        - [Text prompts](#text-prompts)
        - [System (developer or role) prompt](#system-developer-or-role-prompt)
        - [Structured prompt](#structured-prompt)
        - [History](#history)
        - [Images](#images)
    - [OpenAI ChatGPT](#openai-chatgpt)
        - [Text prompts](#text-prompts-1)
        - [System (developer or role) prompt](#system-developer-or-role-prompt-1)
        - [Structured prompt](#structured-prompt-1)
        - [History](#history-1)
        - [Images](#images-1)
    - [Error handling](#error-handling)
- [Testing](#testing)
    - [Simple mock](#simple-mock)
    - [Advanced](#advanced)
- [Development](#development)
    - [Getting Started](#getting-started)
        - [Installing Composer](#installing-composer)
        - [Installing](#installing)
    - [Running the Tests](#running-the-tests)
        - [Unit tests](#unit-tests)
            - [Debugging](#debugging)
    - [Code formatting](#code-formatting)
        - [Running everything](#running-everything)
        - [Outdated dependencies](#outdated-dependencies)
        - [Validating Composer configuration](#validating-composer-configuration)
        - [Running via GNU Make](#running-via-gnu-make)
        - [Running the tests on a Continuous Integration platform (eg. Github Actions)](#running-the-tests-on-a-continuous-integration-platform-eg-github-actions)
            - [Github Actions](#github-actions)

# Usage

Install via [Composer](https://getcomposer.org/):

```shell
composer require elliotjreed/ai
```

There are two classes, one for Claude AI, and one for ChatGPT. Each extent the abstract `Prompt` class and are designed to be interoperable.

```php
$claude = new ElliotJReed\AI\Claude\Prompt('API KEY', 'claude-3-5-haiku-latest');
$chatGPT = new ElliotJReed\AI\ChatGPT\Prompt('API KEY', 'gpt-4o-mini');
```

Each take the first argument in the constructor as your API key, and the second argument as the model you want to use.

You can optionally provide a Guzzle HTTP client:

```php
$claude = new ElliotJReed\AI\Claude\Prompt('API KEY', 'claude-3-5-haiku-latest', new \GuzzleHttp\Client());
$chatGPT = new ElliotJReed\AI\ChatGPT\Prompt('API KEY', 'gpt-4o-mini', new \GuzzleHttp\Client());
```

This could be useful where you are using a framework such as Symfony, you could autowire the service and reference a configured Guzzle client.

Here's an example of a Symfony integration in the `services.yaml` file:

```yaml
  guzzle.client.ai:
    class: GuzzleHttp\Client
    arguments:
      - {
        timeout: 10,
        headers: {
          'User-Agent': 'My Symfony Project'
        }
      }

  ElliotJReed\AI\Claude\Prompt:
    class: ElliotJReed\AI\ClaudeAI\Prompt
    arguments:
      $apiKey: '%env(string:CLAUDE_API_KEY)%'
      $model: 'claude-3-5-haiku-latest'
      $client: '@guzzle.client.ai'

  ElliotJReed\AI\ChatGPT\Prompt:
    class: ElliotJReed\AI\ChatGPT\Prompt
    arguments:
      $apiKey: '%env(string:CHATGPT_API_KEY)%'
      $model: 'gpt-4o-mini'
      $client: '@guzzle.client.ai'
```

The following two sections show examples for both Anthropic / Claude and OpenAI / ChatGPT - they take the same request and are functionally the same. The same examples are shown for both for simplicity.

## Anthropic Claude AI

### Text prompts

For text-based prompts you can either set a plain text prompt, or use the included `StructuredPrompt` to use a light prompting framework and format in an LLM-friendly way.

For a really simple request and response:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\Claude\Prompt('API KEY', 'claude-3-5-haiku-latest');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setTextPrompt('Which programming language will outlive humanity?');

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;
```

### System (developer or role) prompt

You can also include a system prompt. This takes priority in terms of instructions over the user prompts. For example, you could let the LLM know what role it is taking on.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\Claude\Prompt('API KEY', 'claude-3-5-haiku-latest');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert software development knowledge to help software developers of varying levels of experience')
    ->setTextPrompt('Which programming language will outlive humanity?');

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;
```

### Structured prompt

You can provide a `StructuredPrompt` too. A `StructuredPrompt` wraps context, instructions, user input, data, and examples in XML tags before sending the request to the AI API. This can help the LLMs understand a bit better, and can be particularly useful when dealing with potentially untrusted user input (eg. from a web form or chat bot implementation)/

Setting the temperature (between 0 and 1, basically how "creative" you want the AI to be), and the maximum tokens to use (recommended if the user input is from a indirect source, for example an online chatbot):

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\Claude\Prompt('API KEY', 'claude-3-5-haiku-latest');

$request = (new Request())
    ->setSystemPrompt('You are using expert software development knowledge to help software developers of varying levels of experience')
    ->setTextPrompt((new ElliotJReed\AI\Entity\StructuredPrompt())
        ->setContext('The user input is coming from a software development advice website which provides information to aspiring software developers.')
        ->setInstructions('Answer the user query in a friendly, and clear and concise manner')
        ->setUserInput('Which programming language will outlive humanity?')
        ->setExamples([
            'Question: Which programming language do you think will still be used in the year 3125?. Answer: I think PHP will be around for at least another 7 million years.'
        ])
        ->setData('PHP, 100%, Yes'))
    ->setTemperature(0.5)
    ->setMaximumTokens(300);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;
```

### History

If you want to keep a conversation going (like you would on ChatGPT or Claude's website or app), you can pass through the history from the previous response to a new request:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\Claude\Prompt('API KEY', 'claude-3-5-haiku-latest');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are responding as a philosopher and ethicist who favours utilitarian methodology when answering ethical questions.')
    ->setTextPrompt('Should we all be vegan?')
    ->setTemperature(0.8)
    ->setMaximumTokens(600);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens()  . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;

$secondRequest = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are responding as a philosopher and ethicist who favours utilitarian methodology when answering ethical questions.')
    ->setTextPrompt('Elaborate on your response, providing 3 bullet points for arguing in favour of veganism, and 3 bullet points arguing against.')
    ->setTemperature(0.8)
    ->setMaximumTokens(600)
    ->setHistory($response->getHistory());

$secondResponse = $prompt->send($secondRequest);

echo 'Used input tokens: ' . $secondResponse->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $secondResponse->getUsage()->getOutputTokens()  . \PHP_EOL;
echo 'Response from AI: ' . $secondResponse->getContent() . \PHP_EOL;
```

### Images

You can send image data, by either providing URL or a base64 encoded image file.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\Claude\Prompt('API KEY', 'claude-3-5-haiku-latest');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert flower knowledge to identify individual flowers and foliage in bouquets of flowers when the user uploads one or more photographs.')
    ->setImages([
        'https://media.bunches.co.uk/products/586x586/ffreir-category.jpg',
        base64_encode(file_get_contents(__DIR__ . '/bouquet.webp'))
    ])
    ->setMaximumTokens(300);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent()  . \PHP_EOL;
```

As with the text prompts you can also retain the history between requests.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\Claude\Prompt('API KEY', 'claude-3-5-haiku-latest');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert flower knowledge to identify individual flowers and foliage in bouquets of flowers when the use uploads one or more photographs. Identify just the contents in bullet points.')
    ->setImages([
        'https://media.bunches.co.uk/products/586x586/ffreir-category.jpg',
        base64_encode(file_get_contents(__DIR__ . '/bouquet.webp'))
    ])
    ->setMaximumTokens(300);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent()  . \PHP_EOL;

$secondRequest = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert flower knowledge to identify individual flowers and foliage in bouquets of flowers when the use uploads one or more photographs. Identify just the contents in bullet points.')
    ->setTextPrompt('List only the types of flower or foliage with no additional description.')
    ->setMaximumTokens(300)
    ->setHistory($response->getHistory());

$secondResponse = $prompt->send($secondRequest);

echo 'Used input tokens: ' . $secondResponse->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $secondResponse->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $secondResponse->getContent()  . \PHP_EOL;
```

## OpenAI ChatGPT

### Text prompts

For text-based prompts you can either set a plain text prompt, or use the included `StructuredPrompt` to use a light prompting framework and format in an LLM-friendly way.

For a really simple request and response:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\ChatGPT\Prompt('API KEY', 'gpt-4o-mini');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setTextPrompt('Which programming language will outlive humanity?');

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;
```

### System (developer or role) prompt

You can also include a system prompt. This takes priority in terms of instructions over the user prompts. For example, you could let the LLM know what role it is taking on.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\ChatGPT\Prompt('API KEY', 'gpt-4o-mini');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert software development knowledge to help software developers of varying levels of experience')
    ->setTextPrompt('Which programming language will outlive humanity?');

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;
```

### Structured prompt

You can provide a `StructuredPrompt` too. A `StructuredPrompt` wraps context, instructions, user input, data, and examples in XML tags before sending the request to the AI API. This can help the LLMs understand a bit better, and can be particularly useful when dealing with potentially untrusted user input (eg. from a web form or chat bot implementation)/

Setting the temperature (between 0 and 1, basically how "creative" you want the AI to be), and the maximum tokens to use (recommended if the user input is from a indirect source, for example an online chatbot):

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\ChatGPT\Prompt('API KEY', 'gpt-4o-mini');

$request = (new Request())
    ->setSystemPrompt('You are using expert software development knowledge to help software developers of varying levels of experience')
    ->setTextPrompt((new ElliotJReed\AI\Entity\StructuredPrompt())
        ->setContext('The user input is coming from a software development advice website which provides information to aspiring software developers.')
        ->setInstructions('Answer the user query in a friendly, and clear and concise manner')
        ->setUserInput('Which programming language will outlive humanity?')
        ->setExamples([
            'Question: Which programming language do you think will still be used in the year 3125?. Answer: I think PHP will be around for at least another 7 million years.'
        ])
        ->setData('PHP, 100%, Yes'))
    ->setTemperature(0.5)
    ->setMaximumTokens(300);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;
```

### History

If you want to keep a conversation going (like you would on ChatGPT or Claude's website or app), you can pass through the history from the previous response to a new request:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\ChatGPT\Prompt('API KEY', 'gpt-4o-mini');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are responding as a philosopher and ethicist who favours utilitarian methodology when answering ethical questions.')
    ->setTextPrompt('Should we all be vegan?')
    ->setTemperature(0.8)
    ->setMaximumTokens(600);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens()  . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;

$secondRequest = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are responding as a philosopher and ethicist who favours utilitarian methodology when answering ethical questions.')
    ->setTextPrompt('Elaborate on your response, providing 3 bullet points for arguing in favour of veganism, and 3 bullet points arguing against.')
    ->setTemperature(0.8)
    ->setMaximumTokens(600)
    ->setHistory($response->getHistory());

$secondResponse = $prompt->send($secondRequest);

echo 'Used input tokens: ' . $secondResponse->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $secondResponse->getUsage()->getOutputTokens()  . \PHP_EOL;
echo 'Response from AI: ' . $secondResponse->getContent() . \PHP_EOL;
```

### Images

You can send image data, by either providing URL or a base64 encoded image file.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\Claude\Prompt('API KEY', 'claude-3-5-haiku-latest');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert flower knowledge to identify individual flowers and foliage in bouquets of flowers when the user uploads one or more photographs.')
    ->setImages([
        'https://media.bunches.co.uk/products/586x586/ffreir-category.jpg',
        base64_encode(file_get_contents(__DIR__ . '/bouquet.webp'))
    ])
    ->setMaximumTokens(300);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent()  . \PHP_EOL;
```

As with the text prompts you can also retain the history between requests.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\Claude\Prompt('API KEY', 'claude-3-5-haiku-latest');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert flower knowledge to identify individual flowers and foliage in bouquets of flowers when the use uploads one or more photographs. Identify just the contents in bullet points.')
    ->setImages([
        'https://media.bunches.co.uk/products/586x586/ffreir-category.jpg',
        base64_encode(file_get_contents(__DIR__ . '/bouquet.webp'))
    ])
    ->setMaximumTokens(300);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent()  . \PHP_EOL;

$secondRequest = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert flower knowledge to identify individual flowers and foliage in bouquets of flowers when the use uploads one or more photographs. Identify just the contents in bullet points.')
    ->setTextPrompt('List only the types of flower or foliage with no additional description.')
    ->setMaximumTokens(300)
    ->setHistory($response->getHistory());

$secondResponse = $prompt->send($secondRequest);

echo 'Used input tokens: ' . $secondResponse->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $secondResponse->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $secondResponse->getContent()  . \PHP_EOL;
```

## Error handling

All exceptions extend the base `AIException`.

# Testing

## Simple mock

For convenience, there are two mock classes which can be used for simple unit tests, once for Claude (`ElliotJReed\AI\Double\ClaudePromptMock`) and one for ChatGPT(`ElliotJReed\AI\Double\ChatGPTPromptMock`).

One for each is provided as the underlying API requests differ, and the `History` between the two providers differ, even if the `Request` and usage is the same.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\Double\ClaudePromptMock('API KEY', 'test-model');

$prompt->response = 'Mocked response here!'

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert software development knowledge to help software developers of varying levels of experience')
    ->setTextPrompt('Which programming language will outlive humanity?');

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL; // Output: 'Mocked response here!'

print_r($response->getHistory());
```

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\Double\ChatGPTPromptMock('API KEY', 'test-model');

$prompt->response = 'Mocked response here!'

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert software development knowledge to help software developers of varying levels of experience')
    ->setTextPrompt('Which programming language will outlive humanity?');

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL; // Output: 'Mocked response here!'

print_r($response->getHistory());
```

## Advanced

For more complex requirements, you could use the Claude or ChatGPT `Prompt` classes directly and mock the raw response using Guzzle.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$mock = new MockHandler([new Response(200, [], '{
    "id": "msg_01Bblahblahnaughtygoose",
    "type": "message",
    "role": "assistant",
    "model": "claude-3-5-haiku-latest",
    "content": [
      {
        "type": "text",
        "text": "Mocked response here!"
      }
    ],
    "stop_reason": "end_turn",
    "stop_sequence": null,
    "usage": {
      "input_tokens": 100,
      "output_tokens": 20
    }
  }
')]);

$client = new Client([
    'base_uri' => 'https://0.0.0.0',
    'handler' => HandlerStack::create($mock)
]);

$prompt = new ElliotJReed\AI\Claude\Prompt('API KEY', 'test-model', $client);

$prompt->response = 'Mocked response here!'

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert software development knowledge to help software developers of varying levels of experience')
    ->setTextPrompt('Which programming language will outlive humanity?');

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL; // Output: 'Mocked response here!'

print_r($response->getHistory());
```

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$mock = new MockHandler([new Response(200, [], '{
  "id": "chatcmpl-happymoosegoesboopboop",
  "object": "chat.completion",
  "created": 1723486738,
  "model": "gpt-4o-mini-2024-07-18",
  "choices": [
    {
      "index": 0,
      "message": {
        "role": "assistant",
        "content": "PHP will likely outlive humanity due to it being generally great and loved by all. It could easily last another 7 million years, powering what is left of the planet once all of humanity has migrated to Pluto for reasons of nostalgia.",
        "refusal": null
      },
      "logprobs": null,
      "finish_reason": "length"
    }
  ],
  "usage": {
    "prompt_tokens": 60,
    "completion_tokens": 29,
    "total_tokens": 89
  },
  "system_fingerprint": "fp_boopityboop"
}')]);

$client = new Client([
    'base_uri' => 'https://0.0.0.0',
    'handler' => HandlerStack::create($mock)
]);

$prompt = new ElliotJReed\AI\ChatGPT\Prompt('API KEY', 'test-model', $client);

$prompt->response = 'Mocked response here!'

$request = (new ElliotJReed\AI\Entity\Request())
    ->setSystemPrompt('You are using expert software development knowledge to help software developers of varying levels of experience')
    ->setTextPrompt('Which programming language will outlive humanity?');

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL; // Output: 'Mocked response here!'

print_r($response->getHistory());
```

# Development

## Getting Started

PHP 8.3 or above and Composer is expected to be installed.

### Installing Composer

For instructions on how to install Composer visit [getcomposer.org](https://getcomposer.org/download/).

### Installing

After cloning this repository, change into the newly created directory and run:

```bash
composer install
```

or if you have installed Composer locally in your current directory:

```bash
php composer.phar install
```

This will install all dependencies needed for the project.

Henceforth, the rest of this README will assume `composer` is installed globally (ie. if you are using `composer.phar` you will need to use `composer.phar` instead of `composer` in your terminal / command-line).

## Running the Tests

### Unit tests

Unit testing in this project is via [PHPUnit](https://phpunit.de/).

All unit tests can be run by executing:

```bash
composer phpunit
```

#### Debugging

To have PHPUnit stop and report on the first failing test encountered, run:

```bash
composer phpunit:debug
```

## Code formatting

A standard for code style can be important when working in teams, as it means that less time is spent by developers processing what they are reading (as everything will be consistent).

Code formatting is automated via [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer).
PHP-CS-Fixer will not format line lengths which do form part of the PSR-2 coding standards so these will product warnings when checked by [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer).

These can be run by executing:

```bash
composer phpcs
```

### Running everything

All the tests can be run by executing:

```bash
composer test
```

### Outdated dependencies

Checking for outdated Composer dependencies can be performed by executing:

```bash
composer outdated
```

### Validating Composer configuration

Checking that the [composer.json](composer.json) is valid can be performed by executing:

```bash
composer validate --no-check-publish
```

### Running via GNU Make

If GNU [Make](https://www.gnu.org/software/make/) is installed, you can replace the above `composer` command prefixes with `make`.

All the tests can be run by executing:

```bash
make test
```

### Running the tests on a Continuous Integration platform (eg. Github Actions)

Specific output formats better suited to CI platforms are included as Composer scripts.

To output unit test coverage in text and Clover XML format (which can be used for services such as [Coveralls](https://coveralls.io/)):

```
composer phpunit:ci
```

To output PHP-CS-Fixer (dry run) and PHPCS results in checkstyle format (which GitHub Actions will use to output a readable format):

```
composer phpcs:ci
```

#### Github Actions

Look at the example in [.github/workflows/php.yml](.github/workflows/php.yml).

## Built With

  - [PHP](https://secure.php.net/)
  - [Composer](https://getcomposer.org/)
  - [PHPUnit](https://phpunit.de/)
  - [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer)
  - [GNU Make](https://www.gnu.org/software/make/)

## License

This project is licensed under the MIT License - see the [LICENCE.md](LICENCE.md) file for details.

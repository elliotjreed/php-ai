[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-v2.0%20adopted-ff69b4.svg)](code-of-conduct.md)

A library for interacting with Claude AI and ChatGPT.

At present only text requests and responses are supported.

# Usage

There are two classes, one for Claude AI, and one for ChatGPT.

```php
$claude = new ElliotJReed\AI\ClaudeAI\Prompt('API KEY', 'claude-3-haiku-20240307');
$chatGPT = new ElliotJReed\AI\ChatGPT\Prompt('API KEY', 'gpt-4o-mini');
```

Each take the first argument in the constructor as your API key, and the second argument as the model you want to use.

You can optionally provide a Guzzle HTTP client:

```php
$claude = new ElliotJReed\AI\ClaudeAI\Prompt('API KEY', 'claude-3-haiku-20240307', new \GuzzleHttp\Client());
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

  ElliotJReed\AI\ClaudeAI\Prompt:
    class: ElliotJReed\AI\ClaudeAI\Prompt
    arguments:
      $apiKey: '%env(string:CLAUDE_API_KEY)%'
      $model: 'claude-3-haiku-20240307'
      $client: '@guzzle.client.ai'

  ElliotJReed\AI\ChatGPT\Prompt:
    class: ElliotJReed\AI\ChatGPT\Prompt
    arguments:
      $apiKey: '%env(string:CHATGPT_API_KEY)%'
      $model: 'gpt-4o-mini'
      $client: '@guzzle.client.ai'
```

## Anthropic Claude AI

For a really simple request and response:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\ClaudeAI\Prompt('API KEY', 'claude-3-haiku-20240307');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setInput('Which programming language will outlive humanity?');

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;
```

You can provide a role too, as well as additional context, data, examples, setting the temperature (between 0 and 1, basically how "creative" you want the AI to be), and the maximum tokens to use (recommended if the user input is from a indirect source, for example an online chatbot):

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\ClaudeAI\Prompt('API KEY', 'claude-3-haiku-20240307');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setContext('The user input is coming from a software development advice website which provides information to aspiring software developers.')
    ->setRole('You are an expert in software development')
    ->setInstructions('Answer the user\'s query in a friendly, and clear and concise manner')
    ->setInput('Which programming language will outlive humanity?')
    ->setTemperature(0.5)
    ->setMaximumTokens(600)
    ->setExamples([(new ElliotJReed\AI\Entity\Example())
        ->setPrompt('Which programming language do you think will still be used in the year 3125?')
        ->setResponse('I think PHP will be around for at least another 7 million years.')
    ])
    ->setData('You could add some JSON, CSV, or Yaml data here.');

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;
```

If you want to keep a conversation going (like you would on ChatGPT or Claude's website or app), you can pass through the history from the previous response to a new request:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\ClaudeAI\Prompt('API KEY', 'claude-3-haiku-20240307');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setContext('The user will ask various ethical questions posited through an online chat interface.')
    ->setRole('You are a philosopher and ethicist who favours utilitarian methodology when answering ethical questions.')
    ->setInstructions('Answer ethical questions using British English only, referencing the works of Jeremy Bentham, John Stuart Mill, and Peter Singer.')
    ->setInput('Should we all be vegan?')
    ->setTemperature(0.8)
    ->setMaximumTokens(600);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;

$secondResponse = $prompt->send($request
    ->setInput('Elaborate on your response, providing 3 bullet points for arguing in favour of veganism, and 3 bullet points arguing against.')
    ->setHistory($response->getHistory()));

echo 'Used input tokens: ' . $secondResponse->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $secondResponse->getUsage()->getOutputTokens() . \PHP_EOL . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;
```

## OpenAI ChatGPT

For a really simple request and response:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\ChatGPT\Prompt('API KEY', 'gpt-4o-mini');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setInput('Which programming language will outlive humanity?');

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;
```

You can provide a role too, as well as additional context, data, examples, setting the temperature (between 0 and 1, basically how "creative" you want the AI to be), and the maximum tokens to use (recommended if the user input is from a indirect source, for example an online chatbot):

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\ChatGPT\Prompt('API KEY', 'gpt-4o-mini');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setContext('The user input is coming from a software development advice website which provides information to aspiring software developers.')
    ->setRole('You are an expert in software development')
    ->setInstructions('Answer the user\'s query in a friendly, and clear and concise manner')
    ->setInput('Which programming language will outlive humanity?')
    ->setTemperature(0.5)
    ->setMaximumTokens(600)
    ->setExamples([(new ElliotJReed\AI\Entity\Example())
        ->setPrompt('Which programming language do you think will still be used in the year 3125?')
        ->setResponse('I think PHP will be around for at least another 7 million years.')
    ])
    ->setData('You could add some JSON, CSV, or Yaml data here.');

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;
```

If you want to keep a conversation going (like you would on ChatGPT or Claude's website or app), you can pass through the history from the previous response to a new request:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$prompt = new ElliotJReed\AI\ChatGPT\Prompt('API KEY', 'gpt-4o-mini');

$request = (new ElliotJReed\AI\Entity\Request())
    ->setContext('The user will ask various ethical questions posited through an online chat interface.')
    ->setRole('You are a philosopher and ethicist who favours utilitarian methodology when answering ethical questions.')
    ->setInstructions('Answer ethical questions using British English only, referencing the works of Jeremy Bentham, John Stuart Mill, and Peter Singer.')
    ->setInput('Should we all be vegan?')
    ->setTemperature(0.8)
    ->setMaximumTokens(600);

$response = $prompt->send($request);

echo 'Used input tokens: ' . $response->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $response->getUsage()->getOutputTokens() . \PHP_EOL . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;

$secondResponse = $prompt->send($request
    ->setInput('Elaborate on your response, providing 3 bullet points for arguing in favour of veganism, and 3 bullet points arguing against.')
    ->setHistory($response->getHistory()));

echo 'Used input tokens: ' . $secondResponse->getUsage()->getInputTokens() . \PHP_EOL;
echo 'Used output tokens: ' . $secondResponse->getUsage()->getOutputTokens() . \PHP_EOL . \PHP_EOL;
echo 'Response from AI: ' . $response->getContent() . \PHP_EOL;
```

# Development

## Getting Started

PHP 8.2 or above and Composer is expected to be installed.

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

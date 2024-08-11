<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\AI\ChatGPT;

use ElliotJReed\AI\ChatGPT\Prompt;
use ElliotJReed\AI\Entity\Example;
use ElliotJReed\AI\Entity\History;
use ElliotJReed\AI\Entity\Request;
use ElliotJReed\AI\Entity\Role;
use ElliotJReed\AI\Exception\ChatGPTResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class PromptTest extends TestCase
{
    public function testItSendsRequestInXmlFormat(): void
    {
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
                "content": "",
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

        $prompt = new Prompt('API KEY', 'gpt-4o-mini', $client);

        $request = (new Request())
            ->setContext('The user input is coming from a software development advice website which provides information to aspiring software developers.')
            ->setRole('You are an expert in software development')
            ->setInstructions('Answer the user\'s query in a friendly, and clear and concise manner')
            ->setInput('Which programming language will outlive humanity?')
            ->setTemperature(0.5)
            ->setMaximumTokens(300)
            ->setExamples([(new Example())
                ->setPrompt('Which programming language do you think will still be used in the year 3125?')
                ->setResponse('I think PHP will be around for at least another 7 million years.')
            ]);

        $prompt->send($request);

        $requestBody = $mock->getLastRequest()->getBody()->getContents();

        $this->assertJsonStringEqualsJsonString('{
          "max_tokens": 300,
          "messages": [
            {
              "content": [
                {
                  "text": "<?xml version=\"1.0\"?>\n<prompt xmlns=\"https://static.elliotjreed.com\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"https://static.elliotjreed.com https://static.elliotjreed.com/prompt.xsd\"><role><![CDATA[You are an expert in software development]]></role><context><![CDATA[The user input is coming from a software development advice website which provides information to aspiring software developers.]]></context><instructions><![CDATA[Answer the user\'s query in a friendly, and clear and concise manner]]></instructions><user_input><![CDATA[Which programming language will outlive humanity?]]></user_input><examples><example><example_prompt><![CDATA[Which programming language do you think will still be used in the year 3125?]]></example_prompt><example_response><![CDATA[I think PHP will be around for at least another 7 million years.]]></example_response></example></examples></prompt>\n",
                  "type": "text"
                }
              ],
              "role": "user"
            }
          ],
          "model": "gpt-4o-mini",
          "temperature": 0.5
        }', $requestBody);

        $xmlRequestContent = \json_decode($requestBody, true);
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?>
          <prompt xmlns="https://static.elliotjreed.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://static.elliotjreed.com https://static.elliotjreed.com/prompt.xsd">
            <role><![CDATA[You are an expert in software development]]></role>
            <context>The user input is coming from a software development advice website which provides information to aspiring software developers.</context>
            <instructions>Answer the user\'s query in a friendly, and clear and concise manner</instructions>
            <user_input>Which programming language will outlive humanity?</user_input>
            <examples>
              <example>
                <example_prompt>Which programming language do you think will still be used in the year 3125?</example_prompt>
                <example_response>I think PHP will be around for at least another 7 million years.</example_response>
              </example>
            </examples>
          </prompt>
        ', $xmlRequestContent['messages'][0]['content'][0]['text']);
    }

    public function testItSendsRequestWithMessageHistoryInXmlFormat(): void
    {
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
                "content": "",
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

        $prompt = new Prompt('API KEY', 'gpt-4o-mini', $client);

        $request = (new Request())
            ->setContext('The user input is coming from a software development advice website which provides information to aspiring software developers.')
            ->setRole('You are an expert in software development')
            ->setInstructions('Answer the user\'s query in a friendly, and clear and concise manner')
            ->setInput('Which programming language will outlive humanity?')
            ->setTemperature(0.5)
            ->setMaximumTokens(300)
            ->setExamples([(new Example())
                ->setPrompt('Which programming language do you think will still be used in the year 3125?')
                ->setResponse('I think PHP will be around for at least another 7 million years.')
            ])
            ->setHistory([(new History())
                ->setRole(Role::ASSISTANT)
                ->setContent('PHP, Javascript, and Python are good programming languages to learn.')]);

        $prompt->send($request);

        $requestBody = $mock->getLastRequest()->getBody()->getContents();

        $this->assertJsonStringEqualsJsonString('{
          "max_tokens": 300,
          "messages": [
            {
              "content": [
                {
                  "text": "PHP, Javascript, and Python are good programming languages to learn.",
                  "type": "text"
                }
              ],
              "role": "assistant"
            },
            {
              "content": [
                {
                  "text": "<?xml version=\"1.0\"?>\n<prompt xmlns=\"https://static.elliotjreed.com\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"https://static.elliotjreed.com https://static.elliotjreed.com/prompt.xsd\"><role><![CDATA[You are an expert in software development]]></role><context><![CDATA[The user input is coming from a software development advice website which provides information to aspiring software developers.]]></context><instructions><![CDATA[Answer the user\'s query in a friendly, and clear and concise manner]]></instructions><user_input><![CDATA[Which programming language will outlive humanity?]]></user_input><examples><example><example_prompt><![CDATA[Which programming language do you think will still be used in the year 3125?]]></example_prompt><example_response><![CDATA[I think PHP will be around for at least another 7 million years.]]></example_response></example></examples></prompt>\n",
                  "type": "text"
                }
              ],
              "role": "user"
            }
          ],
          "model": "gpt-4o-mini",
          "temperature": 0.5
        }', $requestBody);

        $xmlRequestContent = \json_decode($requestBody, true);
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?>
            <prompt xmlns="https://static.elliotjreed.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://static.elliotjreed.com https://static.elliotjreed.com/prompt.xsd">
              <role>You are an expert in software development</role>
              <context>The user input is coming from a software development advice website which provides information to aspiring software developers.</context>
              <instructions>Answer the user\'s query in a friendly, and clear and concise manner</instructions>
              <user_input>Which programming language will outlive humanity?</user_input>
              <examples>
                <example>
                  <example_prompt>Which programming language do you think will still be used in the year 3125?</example_prompt>
                  <example_response>I think PHP will be around for at least another 7 million years.</example_response>
                </example>
              </examples>
            </prompt>
        ', $xmlRequestContent['messages'][1]['content'][0]['text']);
    }

    public function testItReturnsResponse(): void
    {
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

        $prompt = new Prompt('API KEY', 'gpt-4o-mini', $client);

        $request = (new Request())
            ->setContext('The user input is coming from a software development advice website which provides information to aspiring software developers.')
            ->setRole('You are an expert in software development')
            ->setInstructions('Answer the user\'s query in a friendly, and clear and concise manner')
            ->setInput('Which programming language will outlive humanity?')
            ->setTemperature(0.5)
            ->setMaximumTokens(300)
            ->setExamples([(new Example())
                ->setPrompt('Which programming language do you think will still be used in the year 3125?')
                ->setResponse('I think PHP will be around for at least another 7 million years.')
            ])
            ->setData('PHP, 100%, Yes');

        $response = $prompt->send($request);

        $this->assertSame('chatcmpl-happymoosegoesboopboop', $response->getId());
        $this->assertSame('chat.completion', $response->getType());
        $this->assertSame('gpt-4o-mini-2024-07-18', $response->getModel());
        $this->assertSame(Role::ASSISTANT, $response->getRole());
        $this->assertSame('length', $response->getStopReason());
        $this->assertNull($response->getStopSequence());
        $this->assertSame(
            'PHP will likely outlive humanity due to it being generally great and loved by all. It could easily last another 7 million years, powering what is left of the planet once all of humanity has migrated to Pluto for reasons of nostalgia.',
            $response->getContent()
        );
        $this->assertSame(60, $response->getUsage()->getInputTokens());
        $this->assertSame(29, $response->getUsage()->getOutputTokens());
        $this->assertSame(Role::USER, $response->getHistory()[0]->getRole());
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?>
          <prompt
              xmlns="https://static.elliotjreed.com"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://static.elliotjreed.com https://static.elliotjreed.com/prompt.xsd">
              <role>
                  <![CDATA[You are an expert in software development]]>
              </role>
              <context>
                  <![CDATA[The user input is coming from a software development advice website which provides information to aspiring software developers.]]>
              </context>
              <instructions>
                  <![CDATA[Answer the user\'s query in a friendly, and clear and concise manner]]>
              </instructions>
              <user_input>
                  <![CDATA[Which programming language will outlive humanity?]]>
              </user_input>
              <data>
                  <![CDATA[PHP, 100%, Yes]]>
              </data>
              <examples>
                  <example>
                      <example_prompt>
                          <![CDATA[Which programming language do you think will still be used in the year 3125?]]>
                      </example_prompt>
                      <example_response>
                          <![CDATA[I think PHP will be around for at least another 7 million years.]]>
                      </example_response>
                  </example>
              </examples>
          </prompt>', $response->getHistory()[0]->getContent());
        $this->assertSame(Role::ASSISTANT, $response->getHistory()[1]->getRole());
        $this->assertSame(
            'PHP will likely outlive humanity due to it being generally great and loved by all. It could easily last another 7 million years, powering what is left of the planet once all of humanity has migrated to Pluto for reasons of nostalgia.',
            $response->getHistory()[1]->getContent()
        );
    }

    public function testItThrowsExceptionOnHttpRequestError(): void
    {
        $mock = new MockHandler([new Response(500, [], '{
          "error": {
            "message": "You exceeded your current quota, please check your plan and billing details. For more information on this error, read the docs: https://platform.openai.com/docs/guides/error-codes/api-errors.",
            "type": "insufficient_quota",
            "param": null,
            "code": "insufficient_quota"
          }
        }')]);
        $client = new Client([
            'base_uri' => 'https://0.0.0.0',
            'handler' => HandlerStack::create($mock)
        ]);

        $prompt = new Prompt('API KEY', 'gpt-4o-mini', $client);

        $request = (new Request())
            ->setInstructions('Answer the user\'s query in a friendly, and clear and concise manner')
            ->setInput('Which programming language will outlive humanity?');

        $this->expectException(ChatGPTResponseException::class);
        $this->expectExceptionMessage('insufficient_quota (You exceeded your current quota, please check your plan and billing details. For more information on this error, read the docs: https://platform.openai.com/docs/guides/error-codes/api-errors.)');

        $prompt->send($request);
    }

    public function testItThrowsExceptionOnWhenResponseIsNotInJsonFormat(): void
    {
        $mock = new MockHandler([new Response(500, [], 'NOT JSON')]);
        $client = new Client([
            'base_uri' => 'https://0.0.0.0',
            'handler' => HandlerStack::create($mock)
        ]);

        $prompt = new Prompt('API KEY', 'gpt-4o-mini', $client);

        $request = (new Request())
            ->setInstructions('Answer the user\'s query in a friendly, and clear and concise manner')
            ->setInput('Which programming language will outlive humanity?');

        $this->expectException(ChatGPTResponseException::class);
        $this->expectExceptionMessage('Unexpected ChatGPT API response format');

        $prompt->send($request);
    }
}

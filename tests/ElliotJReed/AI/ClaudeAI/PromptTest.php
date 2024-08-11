<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\AI\ClaudeAI;

use ElliotJReed\AI\ClaudeAI\Prompt;
use ElliotJReed\AI\Entity\Content;
use ElliotJReed\AI\Entity\ContentType;
use ElliotJReed\AI\Entity\Example;
use ElliotJReed\AI\Entity\History;
use ElliotJReed\AI\Entity\Request;
use ElliotJReed\AI\Entity\Role;
use ElliotJReed\AI\Exception\ClaudeResponseException;
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
            "id": "msg_01Bblahblahnaughtygoose",
            "type": "message",
            "role": "assistant",
            "model": "claude-3-haiku-20240307",
            "content": [
              {
                "type": "text",
                "text": "PHP will likely outlive humanity due to it being generally great and loved by all. It could easily last another 7 million years, powering what is left of the planet once all of humanity has migrated to Pluto for reasons of nostalgia."
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

        $prompt = new Prompt('API KEY', 'claude-3-haiku-20240307', $client);

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
                            "text": "<?xml version=\"1.0\"?>\n<prompt xmlns=\"https://static.elliotjreed.com\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"https://static.elliotjreed.com https://static.elliotjreed.com/prompt.xsd\"><context><![CDATA[The user input is coming from a software development advice website which provides information to aspiring software developers.]]></context><instructions><![CDATA[Answer the user\'s query in a friendly, and clear and concise manner]]></instructions><user_input><![CDATA[Which programming language will outlive humanity?]]></user_input><examples><example><example_prompt><![CDATA[Which programming language do you think will still be used in the year 3125?]]></example_prompt><example_response><![CDATA[I think PHP will be around for at least another 7 million years.]]></example_response></example></examples></prompt>\n",
                            "type": "text"
                        }
                    ],
                    "role": "user"
                }
            ],
            "model": "claude-3-haiku-20240307",
            "system": "You are an expert in software development",
            "temperature": 0.5
        }', $requestBody);

        $xmlRequestContent = \json_decode($requestBody, true);
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?>
          <prompt
            xmlns="https://static.elliotjreed.com"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://static.elliotjreed.com https://static.elliotjreed.com/prompt.xsd">
            <context>
              <![CDATA[The user input is coming from a software development advice website which provides information to aspiring software developers.]]>
            </context>
            <instructions>
              <![CDATA[Answer the user\'s query in a friendly, and clear and concise manner]]>
            </instructions>
            <user_input>
              <![CDATA[Which programming language will outlive humanity?]]>
            </user_input>
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
          </prompt>
        ', $xmlRequestContent['messages'][0]['content'][0]['text']);
    }

    public function testItSendsRequestWithMessageHistoryInXmlFormat(): void
    {
        $mock = new MockHandler([new Response(200, [], '{
            "id": "msg_01Bblahblahnaughtygoose",
            "type": "message",
            "role": "assistant",
            "model": "claude-3-haiku-20240307",
            "content": [
              {
                "type": "text",
                "text": "PHP will likely outlive humanity due to it being generally great and loved by all. It could easily last another 7 million years, powering what is left of the planet once all of humanity has migrated to Pluto for reasons of nostalgia."
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

        $prompt = new Prompt('API KEY', 'claude-3-haiku-20240307', $client);

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
                ->setContents([(new Content())
                    ->setType(ContentType::TEXT)
                    ->setText('PHP, Javascript, and Python are good programming languages to learn.')])]);

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
                            "text": "<?xml version=\"1.0\"?>\n<prompt xmlns=\"https://static.elliotjreed.com\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"https://static.elliotjreed.com https://static.elliotjreed.com/prompt.xsd\"><context><![CDATA[The user input is coming from a software development advice website which provides information to aspiring software developers.]]></context><instructions><![CDATA[Answer the user\'s query in a friendly, and clear and concise manner]]></instructions><user_input><![CDATA[Which programming language will outlive humanity?]]></user_input><examples><example><example_prompt><![CDATA[Which programming language do you think will still be used in the year 3125?]]></example_prompt><example_response><![CDATA[I think PHP will be around for at least another 7 million years.]]></example_response></example></examples></prompt>\n",
                            "type": "text"
                        }
                    ],
                    "role": "user"
                }
            ],
            "model": "claude-3-haiku-20240307",
            "system": "You are an expert in software development",
            "temperature": 0.5
        }', $requestBody);

        $xmlRequestContent = \json_decode($requestBody, true);
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?>
          <prompt
            xmlns="https://static.elliotjreed.com"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://static.elliotjreed.com https://static.elliotjreed.com/prompt.xsd">
            <context>
              <![CDATA[The user input is coming from a software development advice website which provides information to aspiring software developers.]]>
            </context>
            <instructions>
              <![CDATA[Answer the user\'s query in a friendly, and clear and concise manner]]>
            </instructions>
            <user_input>
              <![CDATA[Which programming language will outlive humanity?]]>
            </user_input>
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
          </prompt>
        ', $xmlRequestContent['messages'][1]['content'][0]['text']);
    }

    public function testItReturnsResponse(): void
    {
        $mock = new MockHandler([new Response(200, [], '{
            "id": "msg_01Bblahblahnaughtygoose",
            "type": "message",
            "role": "assistant",
            "model": "claude-3-haiku-20240307",
            "content": [
              {
                "type": "text",
                "text": "PHP will likely outlive humanity due to it being generally great and loved by all. It could easily last another 7 million years, powering what is left of the planet once all of humanity has migrated to Pluto for reasons of nostalgia."
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

        $prompt = new Prompt('API KEY', 'claude-3-haiku-20240307', $client);

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

        $this->assertSame('msg_01Bblahblahnaughtygoose', $response->getId());
        $this->assertSame('message', $response->getType());
        $this->assertSame('claude-3-haiku-20240307', $response->getModel());
        $this->assertSame(Role::ASSISTANT, $response->getRole());
        $this->assertSame('end_turn', $response->getStopReason());
        $this->assertNull($response->getStopSequence());
        $this->assertSame(ContentType::TEXT, $response->getContents()[0]->getType());
        $this->assertSame(
            'PHP will likely outlive humanity due to it being generally great and loved by all. It could easily last another 7 million years, powering what is left of the planet once all of humanity has migrated to Pluto for reasons of nostalgia.',
            $response->getContents()[0]->getText()
        );
        $this->assertSame(100, $response->getUsage()->getInputTokens());
        $this->assertSame(20, $response->getUsage()->getOutputTokens());
    }

    public function testItThrowsExceptionOnHttpRequestError(): void
    {
        $mock = new MockHandler([new Response(500, [], '{
          "type": "error",
          "error": {
            "type": "invalid_request_error",
            "message": "messages: something went funny"
          }
        }')]);
        $client = new Client([
            'base_uri' => 'https://0.0.0.0',
            'handler' => HandlerStack::create($mock)
        ]);

        $prompt = new Prompt('API KEY', 'claude-3-haiku-20240307', $client);

        $request = (new Request())
            ->setInstructions('Answer the user\'s query in a friendly, and clear and concise manner')
            ->setInput('Which programming language will outlive humanity?');

        $this->expectException(ClaudeResponseException::class);
        $this->expectExceptionMessage('invalid_request_error (messages: something went funny)');

        $prompt->send($request);
    }
}

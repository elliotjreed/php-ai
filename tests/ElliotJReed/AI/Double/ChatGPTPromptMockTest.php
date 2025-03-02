<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\AI\Double;

use ElliotJReed\AI\Double\ChatGPTPromptMock;
use ElliotJReed\AI\Entity\ContentType;
use ElliotJReed\AI\Entity\Request;
use ElliotJReed\AI\Entity\Role;
use ElliotJReed\AI\Entity\StructuredPrompt;
use PHPUnit\Framework\TestCase;

final class ChatGPTPromptMockTest extends TestCase
{
    public function testItReturnsResponse(): void
    {
        $prompt = new ChatGPTPromptMock('API KEY', 'test-model');

        $request = (new Request())
            ->setSystemPrompt('You are helping software developers of varying levels of experience')
            ->setTextPrompt((new StructuredPrompt())
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

        $this->assertSame('TESTID', $response->getId());
        $this->assertSame('chat.completion', $response->getType());
        $this->assertSame('test-model', $response->getModel());
        $this->assertSame(Role::ASSISTANT, $response->getRole());
        $this->assertSame('length', $response->getStopReason());
        $this->assertNull($response->getStopSequence());
        $this->assertSame(
            'Mocked response from assistant',
            $response->getContent()
        );
        $this->assertSame(25, $response->getUsage()->getInputTokens());
        $this->assertSame(4, $response->getUsage()->getOutputTokens());
        $this->assertSame(Role::USER, $response->getHistory()[0]->getRole());
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0"?>
          <prompt>
            <context>The user input is coming from a software development advice website which provides information to aspiring software developers.</context>
            <instructions>Answer the user query in a friendly, and clear and concise manner</instructions>
            <user_input>Which programming language will outlive humanity?</user_input>
            <data>PHP, 100%, Yes</data>
            <examples>
              <example>Question: Which programming language do you think will still be used in the year 3125?. Answer: I think PHP will be around for at least another 7 million years.</example>
            </examples>
          </prompt>
        ', $response->getHistory()[0]->getContents()[0]->getText());
        $this->assertEquals(ContentType::TEXT, $response->getHistory()[0]->getContents()[0]->getType());
        $this->assertSame(Role::ASSISTANT, $response->getHistory()[1]->getRole());
        $this->assertSame(
            'Mocked response from assistant',
            $response->getHistory()[1]->getContents()[0]->getText()
        );
        $this->assertEquals(ContentType::TEXT, $response->getHistory()[1]->getContents()[0]->getType());
    }

    public function testItSetsCustomResponseAndReturnsResponse(): void
    {
        $prompt = new ChatGPTPromptMock('API KEY', 'test-model');

        $prompt->response = 'Custom response!';

        $request = (new Request())
            ->setSystemPrompt('You are helping software developers of varying levels of experience')
            ->setTextPrompt((new StructuredPrompt())
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

        $this->assertSame('TESTID', $response->getId());
        $this->assertSame('chat.completion', $response->getType());
        $this->assertSame('test-model', $response->getModel());
        $this->assertSame(Role::ASSISTANT, $response->getRole());
        $this->assertSame('length', $response->getStopReason());
        $this->assertNull($response->getStopSequence());
        $this->assertSame(Role::ASSISTANT, $response->getHistory()[1]->getRole());
        $this->assertSame(
            'Custom response!',
            $response->getHistory()[1]->getContents()[0]->getText()
        );
    }
}

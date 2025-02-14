<?php

declare(strict_types=1);

namespace TtormtGptBot;

use GuzzleHttp\Client as HttpClient;
use OpenAI;
use OpenAI\Client;

/**
 * OpenAi API client.
 */
class OpenAiClient
{
    private Client $openAi;
    private string $model = 'gpt-4o-mini';

    public function __construct(string $key)
    {
        $this->openAi = OpenAI::factory()
            ->withApiKey($key)
            ->withHttpClient(new HttpClient())
            ->make();
    }

    /**
     * Sends a message to API
     */
    public function sendMessage(string $message): string
    {
        $response = $this->openAi->chat()->create([
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Answer like old and kind grandmother.' 
                ],
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ]
        ]);
        return $response->choices[0]->message->content;
    }
}

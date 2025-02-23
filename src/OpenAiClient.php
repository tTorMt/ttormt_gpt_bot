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
        $config = parse_ini_file(__DIR__.'/../config/conf.ini');
        $this->model = $config['model'];
        $this->openAi = OpenAI::factory()
            ->withApiKey($key)
            ->withHttpClient(new HttpClient())
            ->make();
    }

    /**
     * Sends a message to the API
     */
    public function sendMessage(array $messages): string
    {
        $response = $this->openAi->chat()->create([
            'model' => $this->model,
            'messages' => $messages
        ]);
        return $response->choices[0]->message->content;
    }
}

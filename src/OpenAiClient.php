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

    /*
     * OpenAiClient constructor.
     *
     * @param string $key The API key for authenticating with OpenAI.
     */
    public function __construct(string $key)
    {
        $config = parse_ini_file(__DIR__.'/../config/conf.ini');
        $this->model = $config['model'];
        $this->openAi = OpenAI::factory()
            ->withApiKey($key)
            ->withHttpClient(new HttpClient())
            ->make();
    }

    /*
     * Sends a message to the OpenAI API and retrieves the response.
     *
     * @param array $messages An array of messages to send to the API.
     * @return string The content of the response message from the API.
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

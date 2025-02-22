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
    private string $caracter = 'Answer like old and kind grandmother.';

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
     * Sends a message to API
     */
    public function sendMessage(string $message): string
    {
        $response = $this->openAi->chat()->create([
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->caracter
                ],
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ]
        ]);
        return $response->choices[0]->message->content;
    }

    public function sendPhoto(string $downloadPath, ?string $caption = ''): string
    {
        $response = $this->openAi->chat()->create([
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->caracter
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $caption
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $downloadPath
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        return $response->choices[0]->message->content;
    }
}

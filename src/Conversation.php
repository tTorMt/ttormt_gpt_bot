<?php

declare(strict_types=1);

namespace TtormtGptBot;

use Exception;

require_once __DIR__ . '/../secret/secret.php';

/**
 * Handles conversation history for a specific user.
 */
class Conversation
{
    private int $userID;
    private StorageHandler $storage;
    private string $message;
    private string $assistant;

    /**
     * Constructor for the Conversation class.
     *
     * @param int $userID The unique identifier of the user.
     */
    public function __construct(int $userID)
    {
        $this->userID = $userID;
        $this->storage = new StorageHandler();
    }

    /**
     * Gets assistant text and stores it.
     * 
     * @return bool If info exists.
     */
    public function initAssistant(): bool
    {
        $assistant = $this->storage->getAssistant($this->userID);
        if ($assistant === false) {
            return false;
        }
        $this->assistant = $assistant;
        return true;
    }

    /**
     * Clears the conversation history for the current user.
     */
    public function clearConversation()
    {
        $this->storage->clearMessages($this->userID);
    }

    /**
     * Sends message to openai api with message history
     */
    public function sendMessage(string $message): string
    {
        $openAI = new OpenAiClient(OPENAI_API_KEY);
        $messages = $this->prepareMessages($message);
        $answer = $openAI->sendMessage($messages);
        $this->storeHistory($answer);
        return $answer;
    }

    /**
     * Sends photo message to openai api with message history.
     */
    public function sendPhoto(string $downloadPath, ?string $caption = ''): string
    {
        $openAI = new OpenAiClient(OPENAI_API_KEY);
        $messages = $this->preparePhoto($downloadPath, $caption);
        $answer = $openAI->sendMessage($messages);
        $this->storeHistory($answer);
        return $answer;
    }

    /**
     * Prepares a message using the conversation history, stores it, and returns messages
     *
     * @param string $question The user's question or input.
     * @return array Messages array
     */
    private function prepareMessages(string $question): array
    {
        $this->message = $question;
        $history = $this->storage->getMessages($this->userID);
        $formattedMessages = [
            [
                'role' => 'system',
                'content' => $this->assistant ?? ''
            ]
        ];

        foreach ($history as $message) {
            $formattedMessages[] = [
                'role' => $message['role'],
                'content' => $message['content']
            ];
        }

        $formattedMessages[] = [
            'role' => 'user',
            'content' => $question
        ];
        return $formattedMessages;
    }

    /**
     * Stores the answer from the OpenAI API in the conversation history.
     *
     * @param string $answer The answer received from the OpenAI API.
     */
    private function storeHistory(string $answer) 
    {
        if (is_null($this->message)) {
            throw new Exception('No message saved');
        }
        $this->storage->storeMessages($this->userID, $this->message, $answer);
    }

    /**
     * Prepares a photo message using the conversation history, stores it, and returns messages
     *
     * @param string $link The URL of the photo.
     * @param string $question The accompanying message.
     * @return array Messages array.
     */
    private function preparePhoto(string $link, string $question): array
    {
        $this->message = $question;
        $history = $this->storage->getMessages($this->userID);
        $formattedMessages = [
            [
                'role' => 'system',
                'content' => $this->assistant ?? ''
            ]
        ];

        foreach ($history as $message) {
            $formattedMessages[] = [
                'role' => $message['role'],
                'content' => $message['content']
            ];
        }

        $formattedMessages[] = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $question
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => $link
                    ]
                ]
            ]
        ];
        return $formattedMessages;
    }
}

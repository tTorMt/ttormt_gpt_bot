<?php

declare(strict_types=1);

namespace TtormtGptBot;

use Exception;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;

require_once __DIR__ . '/../secret/secret.php';

/**
 * Telegram Bot API client class
 */
class TelegramClient
{
    private Client $bot;
    private OpenAiClient $openAi;
    private const string API_LINK = 'https://api.telegram.org/file/bot' . TELEGRAM_BOT_SECRET . '/';

    public function __construct()
    {
        $this->bot = new Client(TELEGRAM_BOT_SECRET);
        $this->openAi = new OpenAiClient(OPENAI_API_KEY);

        $this->bot->command('get_my_id', function (Message $message) {
            $this->getMyId($message);
        });

        $this->bot->command('start', function (Message $message) {
            $this->info($message);
        });

        $this->bot->command('help', function (Message $message) {
            $this->help($message);
        });

        $this->bot->command('new_conversation', function (Message $message) {
            $this->newConversation($message);
        });

        $this->bot->on(function (Update $update) {
            $this->message($update);
        }, function () {
            return true;
        });
    }

    /**
     * The get_my_id command. You can use this id for user authorization
     */
    public function getMyId(Message $message)
    {
        $id = $message->getChat()->getId();
        $this->bot->sendMessage($id, 'Your ID is ' . $id);
    }

    /**
     * The info command. 
     */
    public function info(Message $message)
    {
        $id = $message->getChat()->getId();
        $this->bot->sendMessage($id, 'If you authorized just type your request');
    }

    /**
     * Info on help command
     */
    public function help(Message $message)
    {
        $id = $message->getChat()->getId();
        $this->bot->sendMessage($id, 'Contact admin to use this bot');
    }

    /**
     * Start new conversation. Clear history.
     */
    public function newConversation(Message $message)
    {
        $userID = $message->getChat()->getId();
        $conversation = new Conversation($userID);

        // If assistant initialization returns false it means there is no user with such ID.
        if ($conversation->initAssistant() === false) {
            $this->bot->sendMessage($userID, "You aren't authorized. Sorry.");
            return;
        }

        $conversation->clearConversation();
        $this->bot->sendMessage($userID, "Done!");
    }

    /**
     * Makes conversation with OpenAI API
     */
    public function message(Update $update)
    {
        $message = $update->getMessage();
        $userID = $message->getChat()->getId();
        $conversation = new Conversation($userID);

        // If assistant initialization returns false it means there is no user with such ID.
        if ($conversation->initAssistant() === false) {
            $this->bot->sendMessage($userID, "You aren't authorized. Sorry.");
            return;
        }

        $this->bot->sendMessage($userID, "You're authorized. Sending the message...");        
        
        if (!empty($photo = $message->getPhoto())) {
            $photo = array_pop($photo);
            $fileId = $photo->getFileId();
            $file = $this->bot->getFile($fileId);
            $downloadPath = self::API_LINK . $file->getFilePath();
            $result = $conversation->sendPhoto($downloadPath, $message->getCaption());
        } elseif ($messageText = $message->getText()) {
            $result = $conversation->sendMessage($messageText);
        }
        if (is_null($result)) {
            $this->bot->sendMessage($userID, "No allowed content present");
            return;
        }
        $this->bot->sendMessage($userID, "Got the response...");
        $this->bot->sendMessage($userID, $result, 'Markdown');
    }

    /**
     * Runs the telegram bot
     */
    public function run()
    {
        $this->bot->run();
    }
}

<?php

declare(strict_types=1);

namespace TtormtGptBot;

use Exception;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;

require_once __DIR__ . '/../secret/secret.php';

/**
 * Class TelegramClient
 *
 * A client for interacting with the Telegram Bot API.
 * This class handles incoming messages and commands,
 * allowing users to interact with the bot functionality.
 */
class TelegramClient
{
    private Client $bot;
    private const string API_LINK = 'https://api.telegram.org/file/bot' . TELEGRAM_BOT_SECRET . '/';

    public function __construct()
    {
        $this->bot = new Client(TELEGRAM_BOT_SECRET);

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
     * Sends the user's Telegram ID when the 'get_my_id' command is issued.
     *
     * @param Message $message The incoming message containing the user request.
     */
    public function getMyId(Message $message)
    {
        $id = $message->getChat()->getId();
        $this->bot->sendMessage($id, 'Your ID is ' . $id);
    }

    /**
     * Sends an informational message when the 'start' command is issued.
     *
     * @param Message $message The incoming message containing the user request.
     */
    public function info(Message $message)
    {
        $id = $message->getChat()->getId();
        $this->bot->sendMessage($id, 'If you authorized just type your request');
    }

    /**
     * Provides help information when the 'help' command is issued.
     *
     * @param Message $message The incoming message containing the user request.
     */
    public function help(Message $message)
    {
        $id = $message->getChat()->getId();
        $this->bot->sendMessage($id, 'Contact admin to use this bot');
    }

    /**
     * Initiates a new conversation and clears the conversation history.
     *
     * @param Message $message The incoming message containing the user request.
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
     * Processes incoming messages and sends them to the OpenAI API for processing.
     *
     * @param Update $update The update containing the incoming message and its details.
     */
    public function message(Update $update)
    {
        $message = $update->getMessage();
        $userID = $message->getChat()->getId();
        $conversation = new Conversation($userID);

        // Check if the user is authorized
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

        $formatter = new MessageFormatter($result);
        $this->bot->sendMessage($userID, $formatter->format(), 'MarkdownV2');
    }

    /**
     * Runs the Telegram bot, listening for updates and handling them.
     */
    public function run()
    {
        $this->bot->run();
    }
}

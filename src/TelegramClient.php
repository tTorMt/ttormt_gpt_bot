<?php

declare(strict_types=1);

namespace TtormtGptBot;

use Exception;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;

require_once __DIR__ . '/../secret/secret.php';
require_once __DIR__ . '/../auth/authorized_ids.php';

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

        $this->bot->command('getmyid', function (Message $message) {
            $this->getMyId($message);
        });

        $this->bot->command('start', function (Message $message) {
            $this->info($message);
        });

        $this->bot->command('help', function (Message $message) {
            $this->help($message);
        });

        $this->bot->on(function (Update $update) {
            $this->message($update);
        }, function () {
            return true;
        });
    }

    /**
     * The getmyid command. You can use this id for user authorization
     */
    public function getMyId(Message $message)
    {
        $id = $message->getChat()->getId();
        $this->bot->sendMessage($id, 'Your ID is ' . $id);
    }

    /**
     * The getmyid command. You can use this id for user authorization
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
     * Makes conversation with OpenAI API
     */
    public function message(Update $update)
    {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        if (!in_array($id, AUTHORIZED_IDS)) {
            $this->bot->sendMessage($id, "You aren't authorized. Sorry.");
            return;
        }
        $this->bot->sendMessage($id, "You're authorized. Sending the message...");
        if (!empty($photo = $message->getPhoto())) {
            $photo = array_pop($photo);
            $fileId = $photo->getFileId();
            $file = $this->bot->getFile($fileId);
            $downloadPath = self::API_LINK . $file->getFilePath();
            $result = $this->openAi->sendPhoto($downloadPath, $message->getCaption());
        } elseif ($messageText = $message->getText()) {
            $result = $this->openAi->sendMessage($messageText);
        }
        if (is_null($result)) {
            $this->bot->sendMessage($id, "No allowed content present");
            return;
        }
        $this->bot->sendMessage($id, "Got the response...");
        $this->bot->sendMessage($id, $result, 'Markdown');
    }

    /**
     * Runs the telegram bot
     */
    public function run()
    {
        $this->bot->run();
    }
}

<?php
// Load composer

use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../secret/secret.php';
require __DIR__. '/../auth/authorized_ids.php';

// TELEGRAM_BOT_SECRET;
// TELEGRAM_BOT_NAME;
// AUTHORIZED_IDS;

try {
    $bot = new Client(TELEGRAM_BOT_SECRET);

    $bot->command('getmyid', function (Message $message) use ($bot) {
        $id = $message->getChat()->getId();
        $bot->sendMessage($id, 'Your ID is ' . $id);
    });

    $bot->command('start', function (Message $message) use ($bot) {
        $id = $message->getChat()->getId();
        $bot->sendMessage($id, 'If you authorized just type your request');
    });

    $bot->command('help', function (Message $message) use ($bot) {
        $id = $message->getChat()->getId();
        $bot->sendMessage($id, 'No help. Sorry.:(');
    });

    $bot->run();
} catch (\TelegramBot\Api\Exception $exception) {
    // TO DO error logging
    file_put_contents(__DIR__.'/../errors', $exception->getMessage());
}
